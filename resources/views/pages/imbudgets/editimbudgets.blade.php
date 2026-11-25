<x-app-layout>
    <x-spp-form-styles />


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== EDIT FORM ====== --}}
                <form id="imbudgetForm" class="flex flex-col gap-4" enctype="multipart/form-data"
                    action="{{ route('imbudgets.update', $hash) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            @php
                                $imDoctype = strtoupper(trim((string) ($imbudget->doctype ?? '')));

                                $doctypeLabel = match ($imDoctype) {
                                    'CS'  => 'CS',
                                    'RFP' => 'RFP Non Purchase',
                                    'RP'  => 'RFP',
                                    'CA'  => 'CALR Non Purchase',
                                    'RB'  => 'SPB',
                                    'IS'  => 'Issue',
                                    default => null,
                                };

                                $doctypeClasses = match ($imDoctype) {
                                    'CS'  => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
                                    'RFP' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-800/30 dark:text-emerald-300',
                                    'RP'  => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-800/30 dark:text-indigo-300',
                                    'CA'  => 'bg-orange-100 text-orange-700 dark:bg-orange-800/30 dark:text-orange-300',
                                    'RB'  => 'bg-purple-100 text-purple-700 dark:bg-purple-800/30 dark:text-purple-300',
                                    'IS'  => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-800/30 dark:text-cyan-300',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
                                };
                            @endphp

                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg></span>
                                <div>
                                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Edit IM Budget</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Review the budget lines and explain the request before submitting.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-sm font-bold text-blue-700 ring-1 ring-inset ring-blue-100 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30"><span class="text-[10px] font-semibold uppercase tracking-wider text-blue-400">ID</span>{{ $imbudget->imbudgetid }}</span>

                                @if (!empty($doctypeLabel))
                                    <span class="{{ $doctypeClasses }} inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ $doctypeLabel }}
                                    </span>
                                @endif

                                @if (!empty($sourceDocid))
                                    @if (!empty($sourceUrl))
                                        <a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-100 hover:underline dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30">
                                            {{ $sourceDocid }}
                                        </a>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800/40 dark:text-gray-300">
                                            {{ $sourceDocid }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="p-6">

                        {{-- ===== Header fields ===== --}}
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">

                            {{-- Company --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Company
                                </label>

                                <select disabled
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    <option>{{ $imbudget->cpny_id }}</option>
                                </select>

                                {{-- hidden agar tetap terkirim --}}
                                <input type="hidden" name="cpnyid" value="{{ $imbudget->cpny_id }}">
                            </div>

                            {{-- Department --}}
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Department
                                </label>

                                <select disabled
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                    <option>{{ $imbudget->department_id }}</option>
                                </select>

                                <input type="hidden" name="departementid" value="{{ $imbudget->department_id }}">
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Perpost
                                </label>

                                <input type="text" disabled
                                    class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm text-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400"
                                    value="{{ $imbudget->budget_perpost }}">

                                <input type="hidden" name="perpost" value="{{ $imbudget->budget_perpost }}">
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Purpose
                                </label>
                                <textarea rows="1" name="keperluan" disabled
                                    class="h-10 w-full cursor-not-allowed resize-none overflow-hidden rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm leading-5 text-gray-500 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-400">{{ $imbudget->keperluan }}</textarea>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Reason for unbudgeted/over budget
                                </label>
                                <textarea rows="1" name="imbudgetnote" required
                                    class="min-h-10 w-full resize-y rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm leading-5 text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">{{ $imbudget->imbudgetnote }}</textarea>
                            </div>
                        </div>
                    </div>
                    </div>

                    {{-- ===== Detail ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg></span>
                                        <span>
                                            <span class="block text-base font-bold text-gray-800 dark:text-white">IMBudget Detail</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Budget lines and the amounts you request</span>
                                        </span>
                                    </span>
                                    <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start px-6 pb-6 pt-5">
                                    <div class="overflow-x-auto">
                                        <table class="mb-4 w-full border-separate border-spacing-0 text-sm">
                                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                                <tr>
                                                    <th class="w-10 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">No</th>
                                                    <th class="w-20 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">COA</th>
                                                    <th class="w-24 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Activity</th>
                                                    <th class="w-[16%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Activity Descr</th>
                                                    <th class="w-24 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-right">Amount Expense</th>
                                                    <th class="w-24 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-right">Budget Remain</th>
                                                    <th class="w-24 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-right">Budget Needed</th>
                                                    <th class="w-24 border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-right">Budget Requested</th>
                                                    <th class="w-[14%] border-b border-gray-200 px-3 py-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Note</th>
                                                </tr>
                                            </thead>

                                            <tbody id="imbudgetTable">
                                                @php $rowNo = 0; @endphp
                                                @forelse ($imbudgetdetail as $d)
                                                    @php
                                                        $rowNo++;
                                                        $fmt = fn($v) => is_null($v)
                                                            ? ''
                                                            : number_format((float) $v, 2, ',', '.');
                                                    @endphp
                                                    <tr class="imbudget-row" data-detail-id="{{ $d->id }}">
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">{{ $rowNo }}</td>

                                                        {{-- id detail untuk update --}}
                                                        <input type="hidden" name="detail_id[]"
                                                            value="{{ $d->id }}" />

                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                            <input type="text"
                                                                class="w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100 text-center"
                                                                value="{{ $d->budget_account_id }}" readonly>
                                                            <input type="hidden" name="budget_account_id[]"
                                                                value="{{ $d->budget_account_id }}">
                                                        </td>
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-center">
                                                            <input type="text"
                                                                class="w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100 text-center"
                                                                value="{{ $d->budget_activity_id }}" readonly>
                                                            <input type="hidden" name="budget_activity_id[]"
                                                                value="{{ $d->budget_activity_id }}">
                                                        </td>
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text"
                                                                class="w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100"
                                                                value="{{ $d->budget_activity_descr }}" readonly>
                                                            <input type="hidden" name="budget_activity_descr[]"
                                                                value="{{ $d->budget_activity_descr }}">
                                                        </td>

                                                        {{-- angka hasil agregasi/perhitungan (readonly) --}}
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-right">
                                                            <input type="text"
                                                                class="amountExpenseField w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100 text-right"
                                                                value="{{ $fmt($d->amount_expense ?? 0) }}" readonly>
                                                            <input type="hidden" name="amount_expense[]"
                                                                value="{{ (float) ($d->amount_expense ?? 0) }}">
                                                        </td>
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-right">
                                                            <input type="text"
                                                                class="budgetRemainField w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100 text-right"
                                                                value="{{ $fmt($d->budget_remain ?? 0) }}" readonly>
                                                            <input type="hidden" name="budget_remain[]"
                                                                value="{{ (float) ($d->budget_remain ?? 0) }}">
                                                        </td>
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-right">
                                                            <input type="text"
                                                                class="budgetNeededField w-full border-0 bg-transparent! p-0 text-sm text-gray-800 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100 text-right"
                                                                value="{{ $fmt($d->budget_needed ?? 0) }}" readonly>
                                                            <input type="hidden" name="budget_needed[]"
                                                                value="{{ (float) ($d->budget_needed ?? 0) }}">
                                                        </td>

                                                        {{-- editable --}}
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700 text-right">
                                                            @php
                                                                $req = $d->budget_requested ?? 0;
                                                                // tampil pakai koma
                                                                $reqDisp = number_format((float) $req, 2, ',', '.');
                                                            @endphp
                                                            <input type="text" name="budget_requested[]"
                                                                class="reqField w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-800 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-right"
                                                                value="{{ $reqDisp }}" inputmode="decimal"
                                                                autocomplete="off" placeholder="0,00">
                                                        </td>
                                                        <td class="border-b border-gray-100 px-3 py-2.5 align-middle dark:border-gray-700">
                                                            <input type="text" name="note[]"
                                                                class="w-full rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-sm text-gray-800 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                                                value="{{ $d->note }}">
                                                        </td>

                                                    </tr>
                                                @empty
                                                    <tr class="imbudget-row">
                                                        <td colspan="14"
                                                            class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                            No budget detail. (Generated from CS when approving.)
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </details>
                        </div>
                    </div>


                    {{-- ===== Attachments (optional ditampilkan sesuai kebutuhan) ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                    class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                    <span class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span>
                                        <span>
                                            <span class="block text-base font-bold text-gray-800 dark:text-white">Attachments</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this request</span>
                                        </span>
                                    </span>
                                    <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                            <div id="attachmentsList" class="flex flex-col gap-2 px-6 pt-5">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                                📎</div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">
                                                        {{ $att->display_name }}
                                                    </a>
                                                @else
                                                    <span
                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $att->display_name }} (no link)
                                                    </span>
                                                @endif
                                                <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($att->extention ?? '-') }}
                                                    @if (!empty($att->size))
                                                        • {{ number_format($att->size / 1024, 0) }} KB
                                                    @endif
                                                    @if (!empty($att->created_at))
                                                        •
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="removeAttachment2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                            aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Upload baru --}}
                            <div id="attachmentsContainer" class="px-6 pt-3">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                    <button type="button"
                                        class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mx-6 mb-6 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                                Add Attachment
                            </button>
                        </details>

                        <div
                            class="flex flex-row items-center justify-between gap-4 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                            <button id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>

                                <span>Back</span>
                            </button>

                            <!-- Cancel Button-->
                            <div class="flex flex-row flex-wrap items-center justify-end gap-3">
                                <button type="button" id="cancelBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                    <span id="cancelText">Cancel IM Budget</span>
                                    <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                        </path>
                                    </svg>
                                </button>
                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
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

    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>




    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 mt-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }


        });
    </script>
    <script>
        // --- helper angka: "1.234,56" -> 1234.56
        function toNumber(str) {
            if (!str) return 0;
            let s = String(str).trim();
            // hapus spasi
            s = s.replace(/\s+/g, '');
            // kalau ada baik titik maupun koma, anggap koma adalah desimal (format id)
            const hasComma = s.indexOf(',') !== -1;
            const hasDot = s.indexOf('.') !== -1;
            if (hasComma && hasDot) {
                // hilangkan semua titik (thousand), koma -> titik
                s = s.replace(/\./g, '').replace(/,/g, '.');
            } else if (hasComma) {
                s = s.replace(/,/g, '.');
            }
            return isFinite(s) ? parseFloat(s) : 0;
        }

        $('#imbudgetForm').on('submit', function(e) {
            e.preventDefault();

            // RESET error
            $('#imbudgetTable .is-invalid').removeClass('is-invalid');
            $('#imbudgetTable .error-feedback').remove();

            // Minimal 1 baris "aktif" (punya COA) dan budget_requested >= 0
            let anyActive = false;
            let anyInvalid = false;
            let hasRequestedBelowNeeded = false;
            let requestedBelowNeededMessage = '';

            $('#imbudgetTable tr.imbudget-row').each(function() {
                const $tr = $(this);
                const $coa = $tr.find('input[name="budget_account_id[]"]');
                const $reqVis = $tr.find('input[name="budget_requested[]"]');
                const $neededVis = $tr.find('.budgetNeededField');

                const hasCoa = ($coa.val() || '').trim() !== '';

                // normalisasi input req (ganti tampilan -> hidden numeric)
                const reqNum = toNumber($reqVis.val());
                const neededNum = toNumber($neededVis.val());
                $reqVis.val($reqVis.val().replace(/\./g, '').replace(/,/g,
                    ',')); // biar tampilan tetap id (opsional)

                if (hasCoa) anyActive = true;

                if (hasCoa && reqNum < 0) {
                    $reqVis.addClass('is-invalid').after(
                        '<small class="error-feedback">Budget Requested tidak boleh negatif.</small>');
                    anyInvalid = true;
                } else if (hasCoa && reqNum < neededNum) {
                    const rowNo = $.trim($tr.find('td:first').text()) || '-';
                    $reqVis.addClass('is-invalid').after(
                        '<small class="error-feedback">Budget Requested tidak boleh lebih kecil dari Budget Needed.</small>');
                    anyInvalid = true;
                    hasRequestedBelowNeeded = true;

                    if (!requestedBelowNeededMessage) {
                        requestedBelowNeededMessage =
                            `Baris ${rowNo}: Budget Requested (${formatID(reqNum)}) harus minimal sama dengan Budget Needed (${formatID(neededNum)}).`;
                    }
                }
            });

            if (!anyActive) {
                toastr.error('Minimal ada 1 baris detail dengan COA terisi.');
                return;
            }
            if (hasRequestedBelowNeeded) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Budget Requested tidak valid',
                    text: requestedBelowNeededMessage || 'Budget Requested tidak boleh lebih kecil dari Budget Needed.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#2563eb',
                });

                const $first = $('#imbudgetTable .is-invalid').first();
                if ($first.length) $('html,body').animate({
                    scrollTop: $first.offset().top - 120
                }, 300);
                return;
            }
            if (anyInvalid) {
                toastr.error('Mohon periksa Budget Requested yang tidak valid.');
                const $first = $('#imbudgetTable .is-invalid').first();
                if ($first.length) $('html,body').animate({
                    scrollTop: $first.offset().top - 120
                }, 300);
                return;
            }

            // lock UI + submit AJAX seperti sebelumnya...
            $('#submitBtn, #cancelBtn').prop('disabled', true);
            $('#btnText').text('Processing...');
            showOverlay('Submitting');

            const form = document.getElementById('imbudgetForm');
            const formData = new FormData(form);
            formData.set('_method', 'PUT');

            $.ajax({
                url: form.action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    toastr.success(res.message || "IMBudget updated successfully!");
                    window.location.href = "/imbudgets";
                },
                error: function(xhr) {
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        let msg = 'Mohon periksa input:<br>';
                        Object.keys(errors).forEach(k => {
                            msg += `- ${errors[k].join(', ')}<br>`;
                        });
                        toastr.error(msg);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error! Please check the input.');
                    }
                },
                complete: function() {
                    $('#submitBtn, #cancelBtn').prop('disabled', false);
                    $('#btnText').text('Submit Approval');
                    hideOverlay();
                }
            });
        });
    </script>

    <script>
        /** Format ke gaya Indonesia: 1.234,56 (2 desimal) */
        function formatID(num) {
            if (num === null || num === undefined || isNaN(num)) return '';
            const fixed = Number(num).toFixed(2); // "1234.56"
            const [intStr, decStr] = fixed.split('.');
            const intFmt = intStr.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return intFmt + ',' + decStr;
        }

        /** Validasi + sanitasi input angka/desimal (mendukung "." dan ",") */
        function sanitizeNumericInput(raw) {
            if (!raw) return '';
            let v = String(raw);

            // buang semua karakter selain digit, titik, koma
            v = v.replace(/[^0-9.,]/g, '');

            // rapikan separator beruntun ",,.." atau "..,,"
            v = v.replace(/,{2,}/g, ',').replace(/\.{2,}/g, '.');

            // kalau ada koma & titik sekaligus, biarkan dulu; blur akan normalkan.
            return v;
        }

        $(document).ready(function() {
            const $req = $('#imbudgetTable').find('input[name="budget_requested[]"]');

            // Tambah atribut bantu (opsional, untuk UX)
            $req.attr({
                'autocomplete': 'off',
                'inputmode': 'decimal',
                'placeholder': '0,00',
                'aria-label': 'Budget Requested (angka & desimal)'
            });

            // Saat mengetik: tolak karakter non angka/desimal
            $(document).on('input', 'input[name="budget_requested[]"]', function() {
                const cur = $(this).val();
                const clean = sanitizeNumericInput(cur);
                if (cur !== clean) $(this).val(clean);
            });

            // Blok beberapa key yang mengganggu (mis. minus, huruf e)
            $(document).on('keydown', 'input[name="budget_requested[]"]', function(e) {
                const allowedControl = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
                    'Home', 'End', 'Tab'
                ];
                if (allowedControl.includes(e.key) || (e.ctrlKey || e.metaKey)) return;

                // izinkan digit, koma, titik
                const isDigit = e.key >= '0' && e.key <= '9';
                const isComma = e.key === ',';
                const isDot = e.key === '.';

                if (!(isDigit || isComma || isDot)) {
                    e.preventDefault();
                }
            });

            // Saat paste: sanitasi cepat setelah paste
            $(document).on('paste', 'input[name="budget_requested[]"]', function() {
                const el = this;
                setTimeout(() => {
                    el.value = sanitizeNumericInput(el.value);
                }, 0);
            });

            // Saat blur: normalisasi → ke angka → format Indonesia 2 desimal
            $(document).on('blur', 'input[name="budget_requested[]"]', function() {
                const v = $(this).val().trim();
                if (v === '') return;

                const num = toNumber(v); // fungsi milikmu (sudah ada di view)
                if (!isFinite(num) || num < 0) {
                    // tandai invalid dan kasih pesan
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.error-feedback').length) {
                        $(this).after('<small class="error-feedback">Hanya angka & desimal (≥ 0).</small>');
                    }
                    return;
                }

                // valid → hapus error & format ID
                $(this).removeClass('is-invalid');
                $(this).next('.error-feedback').remove();
                $(this).val(formatID(num));
            });
        });
    </script>

    <script>
        $(document).on('click', '.removeAttachment2', function() {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            // lock UI kecil pada tombol
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

            $.ajax({
                    url: "/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        // animasi keluar biar halus
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });

        // ===== Cancel Button =====
        $('#cancelBtn').click(async function() {
            const confirmed = await Swal.fire({
                icon: 'warning',
                title: 'Cancel IM Budget?',
                text: 'Are you sure you want to cancel this document? This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'No',
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                focusCancel: true,
            });

            if (!confirmed.isConfirmed) return;

            $('#cancelBtn').prop('disabled', true);
            $('#cancelText').text('Cancelling...');
            $('#cancelSpinner').removeClass('hidden');
            showOverlay('Cancelling');

            $.ajax({
                url: "{{ route('imbudgets.cancel', $hash) }}",
                type: 'POST',
                data: {
                    _method: 'PUT',
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    toastr.success(res.message || 'Document cancelled.');
                    window.location.href = "{{ route('imbudgets') }}";
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to cancel document.');
                    $('#cancelBtn').prop('disabled', false);
                    $('#cancelText').text('Cancel IM Budget');
                    $('#cancelSpinner').addClass('hidden');
                    hideOverlay();
                }
            });
        });
    </script>



    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
