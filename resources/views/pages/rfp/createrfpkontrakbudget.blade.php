<x-app-layout>
    <x-wh-form-styles />
    @php
        $isEditMode = ($formMode ?? 'create') === 'edit';
        $isKontrak = strtoupper(trim((string) $rfp->type_po)) === 'KONTRAK';
        $submitUrl = $submitUrl ?? route('rfp.kontrak-budget.submit', ['hash' => $hash]);
        $cancelUrl = $cancelUrl ?? route('rfp.kontrak-budget.cancel', ['hash' => $hash]);
        $pageTitle = $isEditMode ? 'Edit RFP Kontrak Budget ID' : 'Create RFP Kontrak Budget ID';
        $successRedirectUrl = $successRedirectUrl ?? route('rfp');

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
            'D' => 'bg-blue-100 text-blue-700',
            'P' => 'bg-yellow-100 text-yellow-700',
            'C' => 'bg-green-100 text-green-700',
            'H' => 'bg-orange-100 text-orange-700',
            'X', 'R' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };

        $formatDate = function ($value, $withTime = false) {
            if (empty($value)) {
                return '-';
            }

            return \Carbon\Carbon::parse($value)->format($withTime ? 'd M Y H:i:s' : 'd M Y');
        };

        $formatMoney = function ($value) {
            return is_numeric($value) ? 'Rp ' . number_format((float) $value, 2, ',', '.') : '-';
        };

        $typepayment = '-';
        if (strtoupper(trim((string) $rfp->type_po)) === 'KONTRAK') {
            $period = (string) ($rfp->period_payment ?? '');
            $typepayment = strlen($period) >= 7
                ? 'Payment Periode ' . substr($period, 5, 2) . '-' . substr($period, 0, 4)
                : 'Payment Periode -';
        }

        $headerFields = [
            ['label' => 'Company', 'value' => $rfp->cpny_id ?: '-'],
            ['label' => 'Department', 'value' => $rfp->department_id ?: '-'],
            ['label' => 'RP Date', 'value' => $formatDate($rfp->rfp_date)],
            ['label' => 'Created By', 'value' => optional($rfp->creator)->name ?: $rfp->created_by ?: '-'],
            ['label' => 'Vendor ID', 'value' => $rfp->vendor_id ?: '-'],
            ['label' => 'Vendor Name', 'value' => $rfp->vendor_name ?: '-'],
            ['label' => 'PO No', 'value' => $rfp->ponbr ?: '-'],
            ['label' => 'Contract ID', 'value' => $rfp->kontrak_id ?: '-'],
            ['label' => 'CS ID', 'value' => $rfp->cs_id ?: '-'],
            ['label' => 'SPPBJKT ID', 'value' => $rfp->sppbjkt_id ?: '-'],
            ['label' => 'BAST ID', 'value' => $rfp->bastid ?: '-'],
            ['label' => 'IR ID', 'value' => $rfp->ir_id ?: '-'],
            ['label' => 'IR Date', 'value' => $formatDate($rfp->ir_date, true)],
            ['label' => 'IR Submit Date', 'value' => $formatDate($rfp->ir_submit_date, true)],
            ['label' => 'Type PO', 'value' => $rfp->type_po ?: '-'],
            ['label' => 'Type Payment', 'value' => $typepayment],
            ['label' => 'Payment Period', 'value' => $rfp->period_payment ?: '-'],
            ['label' => 'Base Amount', 'value' => $formatMoney($rfp->rfp_base_amount)],
            ['label' => 'Tax Amount', 'value' => $formatMoney($rfp->rfp_tax_amount ?? 0)],
            ['label' => 'Total Amount', 'value' => $formatMoney($rfp->rfp_amount)],
            ['label' => 'Payment Type', 'value' => $rfp->payment_type ?: '-'],
            ['label' => 'Amount Payment', 'value' => is_numeric($rfp->amount_payment ?? null) ? $formatMoney($rfp->amount_payment) : '-'],
            ['label' => 'Terbilang', 'value' => $terbilang ?: '-'],
        ];

        $irNoteValue = trim((string) ($rfp->ir_note ?? ''));
        $purposeValue = trim((string) ($rfp->keperluan ?? ''));
        $showIrNote = $irNoteValue !== '' && $irNoteValue !== '.';
        $showPurpose = !$showIrNote && mb_strlen($purposeValue) > 5;
        $firstBudget = $budgets->first();
        $budgetPerpost = $rfp->period_payment
            ? substr((string) $rfp->period_payment, 0, 4)
            : now()->format('Y');
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">        
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <main class="flex flex-col gap-6 xl:col-span-8">
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <header class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">
                            <span class="mr-2 inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                {{ $pageTitle }}
                            </span>
                            {{ $rfp->rfp_id }}
                        </h2>
                        <span class="{{ $statusClasses }} rounded-full px-4 py-1 text-sm font-semibold">
                            {{ $statusText }}
                        </span>
                    </header>

                    <div class="p-4">
                        @php
                            $rowClass = 'flex flex-col gap-1 p-2 sm:flex-row sm:items-center sm:gap-3';
                            $labelClass = 'text-gray-500 sm:min-w-36';
                            $valueClass = 'break-words font-semibold text-gray-900 dark:text-gray-100 sm:flex-1';
                        @endphp

                        <div class="grid grid-cols-1 gap-x-10 gap-y-1 text-sm md:grid-cols-2">
                            @foreach ($headerFields as $field)
                                <div class="{{ $rowClass }}">
                                    <div class="{{ $labelClass }}">
                                        {{ $field['label'] }}
                                    </div>
                                    <div class="{{ $valueClass }}">
                                        {{ $field['value'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($showIrNote || $showPurpose)
                            <div class="mt-3 rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ $showIrNote ? 'IR Note' : 'Purpose' }}
                                </div>
                                <div class="mt-5 whitespace-pre-line break-words text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $showIrNote ? $irNoteValue : $purposeValue }}
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            </main>

            <aside class="xl:col-span-4">
                <div class="rounded-xl bg-white shadow-sm dark:bg-gray-800">
                    <header class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">
                            Attachment
                        </h2>
                    </header>

                    <div class="max-h-[720px] overflow-y-auto p-4">
                        <div class="space-y-3">
                            @forelse ($stagingAttachments as $attachment)
                                <a href="{{ $attachment->url ?: '#' }}" target="_blank"
                                    class="block rounded-xl border border-gray-100 p-3 text-sm transition hover:border-blue-200 hover:bg-blue-50/50 dark:border-gray-700 dark:hover:bg-gray-700">
                                    <div class="font-semibold text-gray-800 dark:text-gray-100">
                                        {{ $attachment->display_name ?: '-' }}
                                    </div>                                    
                                </a>
                            @empty
                                <div class="rounded-lg border border-dashed border-gray-300 p-4 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
                                    No vendor portal attachment.
                                </div>
                            @endforelse
                        </div>                        
                    </div>
                </div>
            </aside>

            <form id="kontrakBudgetSubmitForm" class="xl:col-span-12">
                @csrf
                <input type="hidden" name="cpnyid" value="{{ $rfp->cpny_id }}">
                @if ($isKontrak)
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <header class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between">
                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">
                            Detail Kontrak Budget
                        </h2>
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="budgetRowCount" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">
                                {{ $budgets->count() }} row
                            </span>
                            <button type="button" id="addKontrakBudget"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span class="text-base leading-none">+</span>
                                Add Budget
                            </button>
                        </div>
                    </header>

                    <div class="overflow-x-auto p-4"><div class="overflow-hidden rounded-xl border border-gray-100 dark:border-white/[0.06]">
                        <table class="min-w-[1300px] w-full text-left text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50/70 dark:border-white/[0.06] dark:bg-white/[0.02]">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">No</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Perpost</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Budget Company</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Business Unit</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Department Fin</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Account</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Activity</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Activity Description</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Amount</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Remaining Budget</th>
                                    <th class="px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Action</th>
                                </tr>
                            </thead>
                            <tbody id="kontrakBudgetTableBody" class="divide-y divide-gray-100 dark:divide-gray-700">
                                @forelse ($budgets as $i => $budget)
                                    @php
                                        $remainingBudget = (float) ($budget->budget_remaining ?? 0);
                                        $amountBudget = (float) ($rfp->rfp_base_amount ?? 0);
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="budget-row-no px-4 py-3">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_perpost ?: '-' }}
                                            <input type="hidden" name="budget_perpost[]" value="{{ $budget->budget_perpost }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_cpny_id ?: '-' }}
                                            <input type="hidden" name="budget_cpny_id[]" value="{{ $budget->budget_cpny_id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_business_unit_id ?: '-' }}
                                            <input type="hidden" name="budget_business_unit_id[]" value="{{ $budget->budget_business_unit_id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_department_fin_id ?: '-' }}
                                            <input type="hidden" name="budget_department_fin_id[]" value="{{ $budget->budget_department_fin_id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_account_id ?: '-' }}
                                            <input type="hidden" name="budget_account_id[]" value="{{ $budget->budget_account_id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_activity_id ?: '-' }}
                                            <input type="hidden" name="budget_activity_id[]" value="{{ $budget->budget_activity_id }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $budget->budget_activity_descr ?: '-' }}
                                            <input type="hidden" name="budget_activity_descr[]" value="{{ $budget->budget_activity_descr }}">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" inputmode="decimal" name="rfp_base_amount[]" value="{{ number_format($amountBudget, 2, ',', '.') }}"
                                                class="budget-amount-input wh-qty w-44!">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            {{ $formatMoney($remainingBudget) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button type="button"
                                                class="deleteKontrakBudgetRow inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90" title="Delete row"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyKontrakBudgetRow">
                                        <td colspan="11" class="p-6 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                            Detail kontrak budget belum tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div></div>

                    <div class="flex items-center justify-between border-t border-gray-100 p-4 dark:border-gray-700">
                        <button type="button" id="cancelRfpBtn"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                            Cancel RFP
                        </button>
                        <button type="submit" id="submitBtn"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <span id="btnText">Submit Approval</span>
                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>
                    </div>
                </section>
                @else
                    <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-t border-gray-200 p-4 dark:border-gray-700">
                            <button type="button" id="cancelRfpBtn"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                Cancel RFP
                            </button>
                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Approval</span>
                                <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </section>
                @endif
            </form>
        </div>

        @if ($isKontrak)
        <div id="kontrakBudgetModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">Select Budget</h3>
                    <button type="button" id="closeKontrakBudgetModal"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                </div>

                <div class="mb-3 flex flex-col gap-2 text-sm md:flex-row md:items-center">
                    <div class="flex items-center gap-2">
                        <input id="kontrakBudgetSearch" type="text" placeholder="Search code/name..."
                            class="h-10 w-64 rounded-full border border-gray-200 bg-white px-4 text-sm text-gray-700 shadow-sm placeholder:text-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                        <button id="kontrakBudgetRefresh" type="button"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            &#8635;
                        </button>
                    </div>
                    <div class="ml-auto flex flex-wrap items-center gap-3 text-gray-600 dark:text-gray-300">
                        <span>Contract ID: <b id="kontrakBudgetContractBadge"></b></span>
                    </div>
                </div>

                <div class="max-h-[60vh] overflow-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Account ID</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Account Descr</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Activity</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Budget Descr</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Remaining Budget</th>
                                <th class="w-24 px-4 py-3 text-center text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody id="kontrakBudgetLookupBody"></tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center justify-between text-sm">
                    <span id="kontrakBudgetCount" class="opacity-80"></span>
                    <div class="space-x-2">
                        <button id="kontrakBudgetPrev" type="button" class="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                        <button id="kontrakBudgetNext" type="button" class="inline-flex h-9 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-gray-50 disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const kontrakBudgetContext = {
            is_kontrak: @json($isKontrak),
            cpnyid: @json($rfp->cpny_id),
            deptid: @json($rfp->department_id),
            perpost: @json($budgetPerpost),
            business_unit_id: @json($firstBudget?->budget_business_unit_id),
            kontrakid: @json($rfp->kontrak_id),
            rfp_cpny_id: @json($rfp->cpny_id),
            rfp_base_amount: @json($rfp->rfp_base_amount),
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatNumber(value, decimals = 0) {
            const number = Number(value || 0);
            return number.toLocaleString('id-ID', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            });
        }

        function formatCurrency(value) {
            return 'Rp ' + formatNumber(value, 2);
        }

        function formatAmountInputValue(value) {
            return formatNumber(parseAmount(value), 2);
        }

        function parseAmount(value) {
            value = String(value ?? '').trim();

            if (value === '') {
                return 0;
            }

            if (value.includes(',') && value.includes('.')) {
                value = value.replace(/\./g, '').replace(',', '.');
            } else if (value.includes(',')) {
                value = value.replace(',', '.');
            }

            const amount = Number(value);
            return Number.isFinite(amount) ? amount : 0;
        }

        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }

        function refreshKontrakBudgetNumbers() {
            const rows = $('#kontrakBudgetTableBody tr.kontrak-budget-row, #kontrakBudgetTableBody tr:not(#emptyKontrakBudgetRow)').filter(function () {
                return $(this).find('td').length > 1;
            });

            rows.each(function (index) {
                $(this).addClass('kontrak-budget-row');
                $(this).find('.budget-row-no').text(index + 1);
            });

            $('#budgetRowCount').text(`${rows.length} row`);

            if (rows.length === 0) {
                if ($('#emptyKontrakBudgetRow').length === 0) {
                    $('#kontrakBudgetTableBody').append(`
                        <tr id="emptyKontrakBudgetRow">
                            <td colspan="11" class="p-6 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                Detail kontrak budget belum tersedia.
                            </td>
                        </tr>
                    `);
                }

                $('#emptyKontrakBudgetRow').removeClass('hidden');
            } else {
                $('#emptyKontrakBudgetRow').addClass('hidden');
            }
        }

        $(function () {
            const $modal = $('#kontrakBudgetModal');
            const $tbody = $('#kontrakBudgetLookupBody');
            const $count = $('#kontrakBudgetCount');

            const state = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
            };

            function openModal() {
                state.search = '';
                state.page = 1;
                $('#kontrakBudgetSearch').val('');
                $('#kontrakBudgetContractBadge').text(kontrakBudgetContext.kontrakid || '-');
                $modal.removeClass('hidden').addClass('flex');
                loadBudget();
            }

            function closeModal() {
                $modal.addClass('hidden').removeClass('flex');
            }

            function loadBudget() {
                $tbody.html('<tr><td colspan="6" class="p-3 text-center">Loading...</td></tr>');

                $.getJSON(@json(route('rfp.kontrak-budget.options', ['hash' => $hash])), {
                    search: state.search,
                    page: state.page,
                    per_page: state.per_page,
                    kontrakid: kontrakBudgetContext.kontrakid,
                })
                .done(function (res) {
                    const rows = (res.data || []).map(item => {
                        const id = item.account_id ?? '';
                        const actId = item.activity_id ?? '';
                        const buId = item.business_unit_id ?? '';
                        const deptFinId = item.department_fin_id ?? '';
                        const actDescr = item.activity_descr ?? '';
                        const available = formatNumber(item.availablebudget ?? 0, 0);
                        const used = formatNumber(item.usedbudget ?? 0, 0);
                        const remaining = formatNumber(item.remaining ?? 0, 0);
                        const accDescr = item.account_descr ?? '';
                        const actDescrLabel = item.act_descr ?? '';

                        return `
                            <tr>
                                <td class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">${escapeHtml(id)}</td>
                                <td class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">${escapeHtml(accDescr)}</td>
                                <td class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">${escapeHtml(actDescrLabel)}</td>
                                <td class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">${escapeHtml(actDescr)}</td>
                                <td class="border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                                    <div class="font-semibold">${remaining}</div>
                                    <div class="text-sm opacity-70">Available: ${available}</div>
                                    <div class="text-sm opacity-70">Used: ${used}</div>
                                </td>
                                <td class="border-b border-gray-100 px-4 py-3 text-center dark:border-gray-700">
                                    <button type="button"
                                        class="chooseKontrakBudget inline-flex h-8 items-center rounded-md border border-blue-100 bg-blue-50 px-3 text-sm font-medium text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0"
                                        data-account-id="${escapeHtml(id)}"
                                        data-account-descr="${escapeHtml(accDescr)}"
                                        data-activity-id="${escapeHtml(actId)}"
                                        data-activity-label="${escapeHtml(actDescrLabel)}"
                                        data-cpny-id="${escapeHtml(item.cpny_id ?? '')}"
                                        data-perpost="${escapeHtml(item.perpost ?? '')}"
                                        data-business-unit-id="${escapeHtml(buId)}"
                                        data-department-fin-id="${escapeHtml(deptFinId)}"
                                        data-activity-descr="${escapeHtml(actDescr)}"
                                        data-remaining="${escapeHtml(item.remaining ?? 0)}">
                                        Choose
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');

                    $tbody.html(rows || '<tr><td colspan="6" class="p-3 text-center">No data</td></tr>');
                    state.total = res.total || 0;
                    $count.text(`Showing ${(res.data || []).length} of ${state.total} items`);

                    const maxPage = Math.ceil((state.total || 0) / state.per_page) || 1;
                    $('#kontrakBudgetPrev').prop('disabled', state.page <= 1);
                    $('#kontrakBudgetNext').prop('disabled', state.page >= maxPage);
                })
                .fail(function () {
                    $tbody.html('<tr><td colspan="6" class="p-3 text-center text-red-600">Failed to load</td></tr>');
                    $count.text('');
                    $('#kontrakBudgetPrev, #kontrakBudgetNext').prop('disabled', true);
                });
            }

            $('#addKontrakBudget').on('click', openModal);
            $('#closeKontrakBudgetModal').on('click', closeModal);

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $modal.is(':visible')) {
                    closeModal();
                }
            });

            $('#kontrakBudgetSearch').on('input', function () {
                state.search = $(this).val().trim();
                state.page = 1;
                loadBudget();
            });

            $('#kontrakBudgetRefresh').on('click', function () {
                $('#kontrakBudgetSearch').val('');
                state.search = '';
                state.page = 1;
                loadBudget();
            });

            $('#kontrakBudgetPrev').on('click', function () {
                if (state.page > 1) {
                    state.page--;
                    loadBudget();
                }
            });

            $('#kontrakBudgetNext').on('click', function () {
                const maxPage = Math.ceil(state.total / state.per_page);

                if (state.page < maxPage) {
                    state.page++;
                    loadBudget();
                }
            });

            $(document).on('click', '.chooseKontrakBudget', function () {
                const $btn = $(this);
                const remainingBudget = parseAmount($btn.data('remaining') || 0);
                const amountBudget = parseAmount(kontrakBudgetContext.rfp_base_amount || 0);

                $('#emptyKontrakBudgetRow').addClass('hidden');
                $('#kontrakBudgetTableBody').append(`
                    <tr class="kontrak-budget-row hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="budget-row-no p-3"></td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('perpost') || '-')}
                            <input type="hidden" name="budget_perpost[]" value="${escapeHtml($btn.data('perpost') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('cpny-id') || '-')}
                            <input type="hidden" name="budget_cpny_id[]" value="${escapeHtml($btn.data('cpny-id') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('business-unit-id') || '-')}
                            <input type="hidden" name="budget_business_unit_id[]" value="${escapeHtml($btn.data('business-unit-id') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('department-fin-id') || '-')}
                            <input type="hidden" name="budget_department_fin_id[]" value="${escapeHtml($btn.data('department-fin-id') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('account-id') || '-')}
                            <input type="hidden" name="budget_account_id[]" value="${escapeHtml($btn.data('account-id') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('activity-id') || '-')}
                            <input type="hidden" name="budget_activity_id[]" value="${escapeHtml($btn.data('activity-id') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            ${escapeHtml($btn.data('activity-descr') || '-')}
                            <input type="hidden" name="budget_activity_descr[]" value="${escapeHtml($btn.data('activity-descr') || '')}">
                        </td>
                        <td class="px-4 py-3">
                            <input type="text" inputmode="decimal" name="rfp_base_amount[]" value="${escapeHtml(formatNumber(amountBudget, 2))}"
                                class="budget-amount-input wh-qty w-44!">
                        </td>
                        <td class="px-4 py-3 text-right">
                            ${formatCurrency(remainingBudget)}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button type="button"
                                class="deleteKontrakBudgetRow inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90" title="Delete row"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                        </td>
                    </tr>
                `);

                refreshKontrakBudgetNumbers();
                closeModal();
            });

            $(document).on('click', '.deleteKontrakBudgetRow', function () {
                $(this).closest('tr').remove();
                refreshKontrakBudgetNumbers();
            });

            $(document).on('blur', '.budget-amount-input', function () {
                $(this).val(formatAmountInputValue($(this).val()));
            });

            $('#cancelRfpBtn').on('click', function () {
                Swal.fire({
                    title: 'Anda Yakin Cancel?',
                    text: 'Status RFP akan diubah menjadi Cancel.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Cancel',
                    cancelButtonText: 'Tidak',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280'
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $('#cancelRfpBtn').prop('disabled', true);
                    showOverlay('Cancelling');

                    $.ajax({
                        url: @json($cancelUrl),
                        type: 'POST',
                        data: {
                            _token: @json(csrf_token())
                        }
                    })
                    .done(function (res) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: res.message || 'RFP berhasil di-cancel.',
                            icon: 'success',
                            timer: 1200,
                            showConfirmButton: false
                        });

                        setTimeout(function () {
                            window.location.href = @json($successRedirectUrl);
                        }, 900);
                    })
                    .fail(function (xhr) {
                        if (xhr.responseJSON?.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Gagal cancel RFP.');
                        }
                    })
                    .always(function () {
                        $('#cancelRfpBtn').prop('disabled', false);
                        hideOverlay();
                    });
                });
            });

            $('#kontrakBudgetSubmitForm').on('submit', function (e) {
                e.preventDefault();

                const rows = $('#kontrakBudgetTableBody tr.kontrak-budget-row, #kontrakBudgetTableBody tr:not(#emptyKontrakBudgetRow)').filter(function () {
                    return $(this).find('input[name="budget_account_id[]"]').length > 0;
                });

                if (kontrakBudgetContext.is_kontrak) {
                    if (rows.length <= 0) {
                        toastr.error('Minimal 1 detail budget harus dipilih.');
                        return;
                    }

                    let invalidMessage = '';
                    let totalAmount = 0;
                    const rfpBaseAmount = parseAmount(kontrakBudgetContext.rfp_base_amount || 0);

                    rows.each(function (index) {
                        const $amount = $(this).find('input[name="rfp_base_amount[]"]');
                        const amount = parseAmount($amount.val());

                        if (amount <= 0) {
                            invalidMessage = `Amount budget baris ${index + 1} harus lebih besar dari 0.`;
                            return false;
                        }

                        totalAmount += amount;
                    });

                    if (invalidMessage !== '') {
                        toastr.error(invalidMessage);
                        return;
                    }

                    if (rfpBaseAmount > 0 && Math.abs(totalAmount - rfpBaseAmount) > 0.01) {
                        toastr.error(`Total amount budget harus sama dengan RFP base amount (${formatCurrency(rfpBaseAmount)}). Total saat ini ${formatCurrency(totalAmount)}.`);
                        return;
                    }
                }

                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                const formData = new FormData(this);

                $.ajax({
                    url: @json($submitUrl),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function (res) {
                    toastr.success(res.message || 'Submit berhasil!');
                    setTimeout(function () {
                        window.location.href = @json($successRedirectUrl);
                    }, 700);
                })
                .fail(function (xhr) {
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        let msg = 'Mohon periksa input:<br>';

                        Object.keys(xhr.responseJSON.errors).forEach(function (key) {
                            msg += `- ${xhr.responseJSON.errors[key].join(', ')}<br>`;
                        });

                        toastr.error(msg);
                    } else if (xhr.responseJSON?.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Terjadi kesalahan.');
                    }
                })
                .always(function () {
                    $('#submitBtn').prop('disabled', false);
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden');
                    hideOverlay();
                });
            });

            refreshKontrakBudgetNumbers();
        });
    </script>
</x-app-layout>
