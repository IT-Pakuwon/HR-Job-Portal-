@php
    $fieldCls = 'w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-500/10 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:ring-indigo-500/20';
    $labelCls = 'mb-1.5 text-xs font-semibold text-gray-600 dark:text-gray-300';
    $ghostBtn = 'inline-flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:bg-transparent dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:text-indigo-300';
    $primaryBtn = 'inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-indigo-500/30 transition hover:bg-indigo-700 focus:outline-none active:scale-95';
    $cancelBtn = 'inline-flex items-center rounded-xl border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700';
    $eyeIcon = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    $docIcon = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
    $plusIcon = 'M12 4.5v15m7.5-7.5h-15';
    $pencilIcon = 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z';
@endphp

<style>
    /* Select2 inside the signer modal — match the rounded fields */
    #signModal .select2-container--default .select2-selection--single {
        height: 42px; border: 1px solid #e5e7eb; border-radius: .75rem; background-color: #f9fafb; display: flex; align-items: center;
    }
    #signModal .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 40px; padding-left: .875rem; font-size: .875rem; color: #1f2937; }
    #signModal .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; right: 8px; }
    #signModal .select2-container--default.select2-container--open .select2-selection--single { border-color: #56b1f3; box-shadow: 0 0 0 4px rgba(86, 177, 243, .12); }
    .dark #signModal .select2-container--default .select2-selection--single { background-color: #374151; border-color: #4b5563; }
    .dark #signModal .select2-container--default .select2-selection--single .select2-selection__rendered { color: #f9fafb; }
</style>

<div class="flex flex-col px-1 py-1">

    {{-- ── Phase 1 · Documents ──────────────────────────────────── --}}
    <div class="flex items-stretch gap-4">
        <div class="flex shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30 ring-4 ring-white dark:ring-gray-800">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="mt-1 w-0.5 flex-1 rounded-full bg-emerald-200 dark:bg-emerald-500/30"></div>
        </div>
        <div class="mb-5 min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $docIcon }}"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 1</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Documents Generated</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Completed
                </span>
            </div>
            <div class="grid grid-cols-1 gap-3 p-4 md:grid-cols-2">
                @foreach ([
                    ['payrollpdf', 'Payroll Confirmation PDF', true],
                    ['offeringForm', 'Offering Letter PDF', false],
                ] as [$formId, $docName, $withRef])
                    <form id="{{ $formId }}" class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 transition hover:border-indigo-200 hover:bg-white hover:shadow-sm dark:border-gray-700/60 dark:bg-gray-700/20 dark:hover:bg-gray-700/40">
                        @csrf
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                        <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                        <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                        <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                        @if ($withRef)
                            <input type="hidden" name="refid" value="{{ $career->refid ?? '' }}">
                            <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                        @endif
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500 dark:bg-rose-500/15 dark:text-rose-300">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $docIcon }}"/></svg>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $docName }}</p>
                                <p class="inline-flex items-center gap-1 text-[11px] text-gray-400">
                                    <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                    PDF · password required
                                </p>
                            </div>
                        </div>
                        <button type="submit" class="{{ $ghostBtn }} shrink-0">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $eyeIcon }}"/></svg>
                            Preview
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Phase 2 · Payroll Confirmation ───────────────────────── --}}
    <div class="flex items-stretch gap-4">
        <div class="flex shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold ring-4 ring-white dark:ring-gray-800 {{ $payrolls->count() > 0 ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-2 border-dashed border-gray-300 bg-white text-gray-400 dark:border-gray-600 dark:bg-gray-800' }}">2</div>
            <div class="mt-1 w-0.5 flex-1 rounded-full bg-gray-200 dark:bg-gray-700"></div>
        </div>
        <div class="mb-5 min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 2</p>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Payroll Confirmation Data</p>
                            <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400">{{ $payrolls->count() }} Record{{ $payrolls->count() === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                </div>
                <button id="addPayrollBtn" class="{{ $primaryBtn }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIcon }}"/></svg>
                    Add
                </button>
            </div>

            <div class="flex items-center gap-2 border-b border-amber-100 bg-amber-50 px-5 py-2.5 dark:border-amber-500/20 dark:bg-amber-900/10">
                <svg class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                <span class="text-xs font-medium text-amber-700 dark:text-amber-400">Salary figures are masked &mdash; password verification is required to reveal, edit or export.</span>
            </div>

            {{-- Payroll Modal --}}
            <div id="payrollModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
                <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="relative overflow-hidden bg-indigo-600 px-6 py-5 text-white">
                        <div class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full bg-white/10"></div>
                        <div class="pointer-events-none absolute -bottom-12 right-24 h-28 w-28 rounded-full bg-white/10"></div>
                        <div class="relative flex items-center gap-3.5">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">Payroll Confirmation</h3>
                                <p class="text-sm text-white/80">Tax, bank and employment details for this applicant.</p>
                            </div>
                        </div>
                    </div>
                    <form id="payrollForm" class="flex-1 space-y-4 overflow-y-auto bg-gray-50/60 px-6 py-5 dark:bg-gray-900/30">
                        @csrf
                        <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                        <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="id" id="payroll_id">

                        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-gray-400">Tax &amp; Bank Details</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="tax_liability" class="{{ $labelCls }}">Dependants</label>
                                    <select name="tax_liability" id="tax_liability" class="{{ $fieldCls }}" required>
                                        <option value="">Select</option>
                                        <option value="TK0">TK0</option>
                                        <option value="K1">K1</option>
                                        <option value="K2">K2</option>
                                        <option value="K3">K3</option>
                                    </select>
                                </div>
                                <div class="flex flex-col">
                                    <label for="npwp_id" class="{{ $labelCls }}">NPWP</label>
                                    <input type="text" name="npwp_id" id="npwp_id" class="{{ $fieldCls }}" required>
                                </div>
                                <div class="flex flex-col">
                                    <label for="bank_account" class="{{ $labelCls }}">Rekening</label>
                                    <input type="text" name="bank_account" id="bank_account" class="{{ $fieldCls }}" required>
                                </div>
                                <div class="flex flex-col">
                                    <label for="bank_name" class="{{ $labelCls }}">Bank</label>
                                    <select name="bank_name" id="bank_name" class="{{ $fieldCls }}" required>
                                        <option value="">Bank Name</option>
                                        <option value="BCA">BCA</option>
                                        <option value="MANDIRI">MANDIRI</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-gray-400">Compensation &amp; Facilities</p>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="net_salary" class="{{ $labelCls }} flex items-center gap-1.5">
                                        Salary
                                        <svg class="h-3 w-3 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
                                    </label>
                                    <input type="text" name="net_salary" id="net_salary" inputmode="numeric" class="money-separator {{ $fieldCls }}" placeholder="0" required>
                                </div>
                                <div class="flex flex-col">
                                    <label for="other_facility" class="{{ $labelCls }}">Fasilitas</label>
                                    <input type="text" name="other_facility" id="other_facility" class="{{ $fieldCls }}" required>
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <p class="mb-3 text-[11px] font-bold uppercase tracking-widest text-gray-400">Employment Period &amp; Status</p>
                            <div class="flex flex-col">
                                <label for="employment_status" class="{{ $labelCls }}">Status Kepegawaian</label>
                                <select name="employment_status" id="employment_status" class="{{ $fieldCls }}" required>
                                    <option value="">Employment Status</option>
                                    <option value="PKWT">PKWT</option>
                                    <option value="PKWTT">PKWTT</option>
                                </select>
                                <div id="contract_term_wrap" class="mt-3 hidden">
                                    <label for="contract_term" class="{{ $labelCls }} block">Contract Term (PKWT)</label>
                                    <select name="contract_term" id="contract_term" class="{{ $fieldCls }}">
                                        <option value="">Select Contract Term</option>
                                        <option value="2">Contract 2 bulan</option>
                                        <option value="3">Contract 3 bulan</option>
                                        <option value="6">Contract 6 bulan</option>
                                        <option value="12">Contract 12 bulan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex flex-col">
                                    <label for="work_start_date" class="{{ $labelCls }}">Tgl Masuk Kerja</label>
                                    <input type="date" name="work_start_date" id="work_start_date" class="{{ $fieldCls }}" required>
                                </div>
                                <div class="flex flex-col">
                                    <label for="availability_date" class="{{ $labelCls }}">Tgl Selesai Kerja</label>
                                    <input type="date" name="availability_date" id="availability_date"
                                        class="w-full cursor-not-allowed rounded-xl border border-dashed border-gray-200 bg-gray-100 px-3.5 py-2.5 text-sm text-gray-600 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        readonly>
                                </div>
                            </div>
                        </section>

                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" id="closeModal" class="{{ $cancelBtn }}">Batal</button>
                            <button type="submit" class="{{ $primaryBtn }} px-5 py-2.5">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Payroll records (cards) --}}
            <div class="space-y-3 p-4">
                @forelse ($payrolls as $p)
                    <div data-id="{{ $p->id }}" class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700/60 dark:bg-gray-800">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-300">{{ $p->employment_status }}@if($p->contract_term) · Contract {{ $p->contract_term }} bulan @endif</span>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-700 dark:text-gray-300">{{ $p->tax_liability }}</span>
                            </div>
                            <button class="editPayrollBtn {{ $ghostBtn }} !px-3 !py-1.5">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pencilIcon }}"/></svg>
                                Edit
                            </button>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700/60 dark:bg-gray-700/20">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">NPWP</dt>
                                <dd class="mt-1 break-all text-sm font-medium text-gray-800 dark:text-gray-100">{{ $p->npwp_id ?: '—' }}</dd>
                            </div>
                            <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700/60 dark:bg-gray-700/20">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Rekening</dt>
                                <dd class="mt-1 flex flex-wrap items-center gap-1.5 text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ $p->bank_account ?: '—' }}
                                    @if ($p->bank_name)
                                        <span class="rounded-md bg-sky-100 px-1.5 py-0.5 text-[10px] font-bold text-sky-700 dark:bg-sky-500/20 dark:text-sky-300">{{ $p->bank_name }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="col-span-2 rounded-xl border border-amber-100 bg-amber-50/70 p-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                                <dt class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-amber-600 dark:text-amber-300">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                    Gaji
                                </dt>
                                <dd class="mt-1 flex items-center gap-2">
                                    <span class="salary-mask text-base font-bold tabular-nums text-gray-900 dark:text-white" data-id="{{ $p->id }}">••••••</span>
                                    <button type="button"
                                        class="revealSalaryBtn rounded-lg bg-white px-1.5 py-1 text-gray-500 shadow-sm ring-1 ring-amber-200 transition hover:bg-amber-100 dark:bg-gray-700 dark:text-gray-300 dark:ring-amber-500/30 dark:hover:bg-gray-600"
                                        data-id="{{ $p->id }}" aria-label="Reveal salary">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                    </button>
                                </dd>
                            </div>
                            <div class="col-span-2 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700/60 dark:bg-gray-700/20">
                                <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Fasilitas</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-800 dark:text-gray-100">{{ $p->other_facility ?: '—' }}</dd>
                            </div>
                            <div class="col-span-2 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700/60 dark:bg-gray-700/20">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                                </span>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tgl Masuk</dt>
                                    <dd class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $p->work_start_date ? \Carbon\Carbon::parse($p->work_start_date)->translatedFormat('d F Y') : '—' }}</dd>
                                </div>
                            </div>
                            <div class="col-span-2 flex items-center gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700/60 dark:bg-gray-700/20">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/></svg>
                                </span>
                                <div>
                                    <dt class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tgl Selesai</dt>
                                    <dd class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $p->availability_date ? \Carbon\Carbon::parse($p->availability_date)->translatedFormat('d F Y') : '—' }}</dd>
                                </div>
                            </div>
                        </dl>
                    </div>
                @empty
                    <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-200 py-10 text-center dark:border-gray-700">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-2xl dark:bg-indigo-500/15">💳</span>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No payroll data found</p>
                        <p class="text-xs text-gray-400">Click <span class="font-semibold">Add</span> to enter the payroll confirmation.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Phase 3 · Approval Chain ─────────────────────────────── --}}
    <div class="flex items-start gap-4">
        <div class="flex shrink-0 flex-col items-center">
            <div class="flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold ring-4 ring-white dark:ring-gray-800 {{ $sign->count() > 0 ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-2 border-dashed border-gray-300 bg-white text-gray-400 dark:border-gray-600 dark:bg-gray-800' }}">3</div>
        </div>
        <div class="min-w-0 flex-1 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700/60 dark:bg-gray-800">

            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-gray-700/60">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487 18.55 2.8a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                    </span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Phase 3</p>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-bold text-gray-900 dark:text-gray-100">Approval Chain</p>
                            <span class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-semibold text-violet-600 dark:bg-violet-900/20 dark:text-violet-300">{{ $sign->count() }} Signer{{ $sign->count() === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                </div>
                <button id="addSignBtn" class="{{ $primaryBtn }}">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIcon }}"/></svg>
                    Add Sign
                </button>
            </div>

            {{-- Sign Modal --}}
            <div id="signModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
                <div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="relative overflow-hidden bg-indigo-600 px-6 py-5 text-white">
                        <div class="pointer-events-none absolute -right-8 -top-10 h-36 w-36 rounded-full bg-white/10"></div>
                        <div class="pointer-events-none absolute -bottom-12 right-24 h-28 w-28 rounded-full bg-white/10"></div>
                        <div class="relative flex items-center gap-3.5">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                            </span>
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">Add Signers</h3>
                                <p class="text-sm text-white/80">Set the approval order for this payroll confirmation.</p>
                            </div>
                        </div>
                    </div>
                    <form id="signForm" class="flex-1 overflow-y-auto bg-gray-50/60 px-6 py-5 dark:bg-gray-900/30">
                        @csrf
                        <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                        <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                        <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                        <input type="hidden" name="id" id="sign_id">

                        <div id="signRows" class="space-y-3"></div>

                        <div class="mt-3">
                            <button type="button" id="addSignRow"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-gray-300 px-3 py-2.5 text-sm font-semibold text-gray-500 transition hover:border-indigo-400 hover:bg-indigo-50/50 hover:text-indigo-600 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:border-indigo-500 dark:hover:text-indigo-400">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIcon }}"/></svg>
                                Add Signer
                            </button>
                        </div>

                        <div class="mt-5 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700/60">
                            <button type="button" id="closeModalsign" class="{{ $cancelBtn }}">Batal</button>
                            <button type="submit" class="{{ $primaryBtn }} px-5 py-2.5">Simpan</button>
                        </div>
                    </form>

                    <template id="signRowTemplate">
                        <div class="sign-row relative rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-gray-700/60 dark:bg-gray-800">
                            <button type="button"
                                class="removeSignRow absolute right-3 top-3 inline-flex h-7 w-7 items-center justify-center rounded-lg text-gray-300 transition hover:bg-red-50 hover:text-red-500 focus:outline-none dark:text-gray-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                                title="Delete row" aria-label="Delete row">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                    aria-hidden="true">
                                    <path d="M3 6h18" />
                                    <path d="M8 6V4h8v2" />
                                    <path d="M19 6l-1 14H6L5 6" />
                                    <path d="M10 11v5M14 11v5" />
                                </svg>
                            </button>
                            <div class="grid gap-3 pr-9" style="grid-template-columns: 96px minmax(0, 1.15fr) minmax(0, 0.85fr);">
                                <div class="flex flex-col">
                                    <label class="{{ $labelCls }}">Urutan</label>
                                    <select name="aprvid[]" class="{{ $fieldCls }}" required>
                                        <option value="" disabled selected>Urutan</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex flex-col">
                                    <label class="{{ $labelCls }}">Nama</label>
                                    <select name="aprvusername[]" class="sign-employee-select {{ $fieldCls }}" required>
                                        <option value="">Select Employee</option>
                                        @foreach ($userlist as $u)
                                            <option value="{{ $u->username }}" data-name="{{ $u->name }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="aprvname[]" class="aprvname-input">
                                </div>
                                <div class="flex flex-col">
                                    <label class="{{ $labelCls }}">Jabatan</label>
                                    <input type="text" name="jabatan[]" class="{{ $fieldCls }}" required>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Sign List (approval order) --}}
            <div id="signList" class="px-5 py-5">
                @forelse ($sign as $p)
                    <div class="sign-row-item flex items-stretch gap-4" data-id="{{ $p->id }}">
                        <div class="flex shrink-0 flex-col items-center">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-violet-600 text-sm font-bold text-white shadow-sm shadow-violet-500/30 ring-4 ring-white dark:ring-gray-800">{{ $p->aprvid }}</div>
                            @if (!$loop->last)
                                <div class="mt-1 w-0.5 flex-1 rounded-full bg-violet-200 dark:bg-violet-500/30"></div>
                            @endif
                        </div>
                        <div class="mb-3 flex min-w-0 flex-1 flex-wrap items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-white p-3.5 shadow-sm transition hover:shadow-md dark:border-gray-700/60 dark:bg-gray-800">
                            <div class="flex min-w-0 items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-800 text-sm font-bold text-white dark:bg-gray-600">{{ strtoupper(substr($p->name, 0, 1)) }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-900 dark:text-gray-100">{{ $p->name }}</p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $p->jabatan }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button class="editsignBtn {{ $ghostBtn }} !px-3 !py-1.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pencilIcon }}"/></svg>
                                    Edit
                                </button>
                                <button class="deletesignBtn inline-flex items-center gap-1.5 rounded-xl border border-red-200 bg-white px-3 py-1.5 text-sm font-semibold text-red-500 transition hover:bg-red-50 focus:outline-none active:scale-95 dark:border-red-800/40 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-500/10">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-200 py-10 text-center dark:border-gray-700">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-violet-50 text-2xl dark:bg-violet-500/15">✍️</span>
                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No Sign data found</p>
                        <p class="text-xs text-gray-400">Add signers to set the approval order.</p>
                    </div>
                @endforelse
            </div>

            {{-- Password Verify Modal --}}
            <div id="salaryPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
                <div class="w-full max-w-sm overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">
                    <div class="flex flex-col items-center gap-2 px-6 pt-6 text-center">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        </span>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Verifikasi Password</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Masukkan password akun Anda untuk melanjutkan.</p>
                    </div>
                    <form id="salaryPasswordForm" class="p-6">
                        @csrf
                        <input type="hidden" id="salary_payroll_id" name="payroll_id">
                        <div class="mb-5">
                            <label class="{{ $labelCls }} block">Password Anda</label>
                            <input type="password" id="salary_password" name="password" autocomplete="current-password" class="{{ $fieldCls }}" required>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" id="salaryModalCancel" class="{{ $cancelBtn }}">Batal</button>
                            <button type="submit" class="{{ $primaryBtn }} px-5 py-2.5">Verifikasi</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
<script>
    // === GLOBALS ===
    let _pendingAction = null; // 'pdf-payroll' | 'pdf-offering' | 'edit-payroll' | 'reveal-salary'
    let _pendingFormEl = null; // form element untuk PDF
    let _pendingPayrollId = null; // id payroll untuk edit/reveal

    function openPasswordModal() {
        // tampilkan modal password yang SUDAH ada: #salaryPasswordModal
        $('#salary_password').val('');
        $('#salaryPasswordModal').removeClass('hidden').addClass('flex');
        setTimeout(() => $('#salary_password').trigger('focus'), 0);
    }
</script>

<script>
    $('#payrollpdf').on('submit', function(e) {
        e.preventDefault();
        _pendingAction = 'pdf-payroll';
        _pendingFormEl = this;
        openPasswordModal();
    });
</script>

<script>
    $('#offeringForm').on('submit', function(e) {
        e.preventDefault();
        _pendingAction = 'pdf-offering';
        _pendingFormEl = this;
        openPasswordModal();
    });
</script>
<script>
    $('#addPayrollBtn').click(function() {
        $('#payrollForm')[0].reset();
        $('#payroll_id').val('');
        $('#net_salary').val('');
        toggleContractTerm($('#employment_status').val() || '');
        $('#payrollModal').removeClass('hidden');
    });

    $('.editPayrollBtn').click(function() {
        _pendingAction = 'edit-payroll';
        _pendingPayrollId = $(this).closest('[data-id]').data('id');
        openPasswordModal();
    });



    $('#closeModal').click(function() {
        $('#payrollModal').addClass('hidden');
    });

    $('#payrollForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);

        // --- bersihkan net_salary sebelum serialize ---
        const $net = $('#net_salary');
        const displayedSalary = $net.val(); // simpan tampilan "1.234.567"
        const cleanedSalary = displayedSalary.replace(/\D/g, ''); // "1234567"
        $net.val(cleanedSalary);

        let url = form.find('#payroll_id').val() ?
            "{{ route('payrollconfirm.update') }}" :
            "{{ route('payrollconfirm.store') }}";

        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success('Data payroll berhasil disimpan.');
                    setTimeout(() => location.reload(), 1000); // reload setelah toastr tampil
                } else {
                    toastr.error('Gagal menyimpan data payroll.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 409 && xhr.responseJSON?.message) {
                    toastr.warning(xhr.responseJSON
                        .message); // pesan duplikat payroll atau onboarding
                } else {
                    toastr.error('Terjadi kesalahan sistem. Coba lagi.');
                }
            },
            complete: function() {
                // --- kembalikan tampilan input ke format ribuan jika tidak reload (mis. error) ---
                $net.val(formatThousandsID(displayedSalary));
            }
        });
    });
</script>
<script>
    $('#salaryPasswordForm')
        .off('submit.payroll')
        .on('submit.payroll', function(e) {
            e.preventDefault();
            const pwd = $('#salary_password').val();

            if (_pendingAction === 'pdf-payroll') {
                sendPdfRequest("{{ route('payrollconfirmation.pdf') }}", _pendingFormEl, pwd);
            } else if (_pendingAction === 'pdf-offering') {
                sendPdfRequest("{{ route('offeringletter.pdf') }}", _pendingFormEl, pwd);
            } else if (_pendingAction === 'edit-payroll') {
                loadPayrollForEdit(_pendingPayrollId, pwd);
            } else if (_pendingAction === 'reveal-salary') {
                $.post("{{ route('payrollconfirm.reveal') }}", {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        payroll_id: _pendingPayrollId,
                        password: pwd
                    })
                    .done(function(resp) {
                        if (resp && resp.success) {
                            const formatted = formatThousandsID(String(resp.salary));
                            $('.salary-mask[data-id="' + _pendingPayrollId + '"]').text(formatted);
                            $('.revealSalaryBtn[data-id="' + _pendingPayrollId + '"] i[data-lucide]')
                                .attr('data-lucide', 'eye-off');
                            if (window.lucide) lucide.createIcons();
                            toastr.success('Gaji ditampilkan.');
                            $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
                        } else {
                            toastr.error(resp?.message || 'Gagal verifikasi.');
                        }
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 401) toastr.error('Password salah.');
                        else if (xhr.status === 403) toastr.error('Anda tidak memiliki akses.');
                        else toastr.error('Terjadi kesalahan. Coba lagi.');
                    });
            }
        });



    function sendPdfRequest(url, formEl, pwd) {
        const fd = new FormData(formEl);
        fd.append('password', pwd);
        $.ajax({
            url,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(blob, status, xhr) {
                const ct = xhr.getResponseHeader('Content-Type') || '';
                if (ct.includes('pdf')) {
                    const pdfUrl = URL.createObjectURL(blob);
                    window.open(pdfUrl, '_blank');
                    toastr.success('Dokumen dibuka.');
                    $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
                } else {
                    toastr.error('Gagal buka dokumen.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) toastr.error('Password salah.');
                else if (xhr.status === 403) toastr.error('Anda tidak memiliki akses.');
                else if (xhr.status === 422) toastr.error('Data tidak valid.');
                else toastr.error('Terjadi kesalahan sistem. Coba lagi.');
            }
        });
    }

    function loadPayrollForEdit(id, pwd) {
        $.get('/payrollconfirm/' + id, {
            password: pwd
        }, function(data) {
            for (let key in data) {
                $('[name="' + key + '"]').val(data[key]);
            }
            if (data.net_salary != null) {
                $('#net_salary').val(formatThousandsID(String(data.net_salary)));
            } else {
                $('#net_salary').val('');
            }
            $('#payroll_id').val(data.id);
            $('#payrollModal').removeClass('hidden');
            $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
        }).fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Akses ditolak');
        });
    }
</script>

<script>
    $('#addSignBtn').click(function() {
        $('#signForm')[0].reset();
        $('#sign_id').val('');
        $('#signModal').removeClass('hidden');
    });

    $('.editsignBtn').click(function() {
        var id = $(this).closest('.sign-row-item').data('id');
        $.get('/signconfirm/' + id, function(data) {
            $('[name="aprvid"]').val(data.aprvid);
            $('[name="aprvusername"]').val(data.name);
            $('[name="jabatan"]').val(data.jabatan);
            $('#sign_id').val(data.id);
            $('#signModal').removeClass('hidden');
        });
    });


    $('#closeModalsign').click(function() {
        $('#signModal').addClass('hidden');
    });
</script>

<script>
    // Guard global kecil utk cegah double submit
    let submittingSign = false;

    $(document)
        .off('submit.sign', '#signForm')
        .on('submit.sign', '#signForm', function(e) {
            e.preventDefault();

            if (submittingSign) return;
            submittingSign = true;

            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const btnHtml = $btn.html();

            $btn.prop('disabled', true)
                .addClass('opacity-60 cursor-not-allowed')
                .html('Menyimpan…');

            const url = $form.find('#sign_id').val() ?
                "{{ route('signconfirm.update') }}" :
                "{{ route('signconfirm.store') }}";

            $.ajax({
                type: 'POST',
                url,
                data: $form.serialize(),
                success: function(resp) {
                    if (resp && resp.success) {
                        toastr.success('Data sign berhasil disimpan.');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        toastr.error('Gagal menyimpan data sign.');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 409 && xhr.responseJSON?.message) {
                        toastr.warning(xhr.responseJSON.message);
                    } else {
                        toastr.error('Terjadi kesalahan sistem. Coba lagi.');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false)
                        .removeClass('opacity-60 cursor-not-allowed')
                        .html(btnHtml);
                    submittingSign = false;
                }
            });
        });
</script>


<script>
    (function() {
        const rows = $('#signRows');
        const tpl = document.getElementById('signRowTemplate');

        function initEmployeeSelect($scope) {
            $scope.find('.sign-employee-select').each(function() {
                const $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }

                $select.select2({
                    dropdownParent: $('#signModal'),
                    placeholder: 'Search Employee',
                    allowClear: true,
                    width: '100%'
                });
            });
        }

        function addRow() {
            const node = tpl.content.cloneNode(true);
            rows.append(node);
            initEmployeeSelect(rows.find('.sign-row').last());
            toggleRemoveButtons();
        }

        function toggleRemoveButtons() {
            const total = rows.find('.sign-row').length;
            rows.find('.removeSignRow').toggleClass('hidden', total <= 1);
        }

        // Add Row
        $('#addSignRow').on('click', addRow);

        // Remove Row (event delegation)
        $(document).on('click', '.removeSignRow', function() {
            const $row = $(this).closest('.sign-row');
            const $employeeSelect = $row.find('.sign-employee-select');
            if ($employeeSelect.hasClass('select2-hidden-accessible')) {
                $employeeSelect.select2('destroy');
            }
            $row.remove();
            toggleRemoveButtons();
        });

        // Buka modal: jika belum ada baris, buat satu baris
        $('#addSignBtn').on('click', function() {
            rows.empty();
            addRow();
            $('#sign_id').val('');
            $('#signModal').removeClass('hidden');
        });

        // (Opsional) Prefill saat edit single: isi baris pertama
        $('.editsignBtn').on('click', function() {
            const id = $(this).closest('.sign-row-item').data('id');
            $.get('/signconfirm/' + id, function(data) {
                rows.empty();
                addRow();
                const row = rows.find('.sign-row').first();
                row.find('[name="aprvid[]"]').val(data.aprvid);
                row.find('[name="aprvusername[]"]').val(data.aprvusername).trigger('change');
                row.find('[name="jabatan[]"]').val(data.jabatan);
                $('#sign_id').val(data.id);
                $('#signModal').removeClass('hidden');
            });
        });

        // Tutup modal
        $('#closeModalsign').on('click', function() {
            $('#signModal').addClass('hidden');
        });

        // Init
        toggleRemoveButtons();
    })();
</script>

<script>
    // isi hidden saat user memilih nama
    $(document).on('change', 'select[name="aprvusername[]"]', function() {
        const opt = $(this).find('option:selected');
        const name = opt.data('name') ?? opt.text();

        const row = $(this).closest('.sign-row').length ?
            $(this).closest('.sign-row') :
            $(this).parent();

        row.find('input.aprvname-input').val(name);
    });

    // opsional: saat buka modal, trigger change agar hidden terisi kalau ada default value
    $('#addSignBtn').on('click', function() {
        $('select[name="aprvusername[]"]').trigger('change');
    });
</script>

<script>
    // Hapus row Sign
    $(document).on('click', '.deletesignBtn', function() {
        const $row = $(this).closest('.sign-row-item');
        const id = $row.data('id');

        if (!confirm('Hapus data sign ini?')) return;

        $.ajax({
            url: '/signconfirm/' + id,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(resp) {
                if (resp && resp.success) {
                    toastr.success('Data sign berhasil dihapus.');
                    $row.remove();

                    if ($('#signList .sign-row-item').length === 0) {
                        $('#signList').html(
                            '<div class="py-6 text-center text-sm italic text-gray-400 dark:text-gray-500">No Sign data found.</div>'
                        );
                    }
                } else {
                    toastr.error('Gagal menghapus data sign.');
                }
            },
            error: function(xhr) {
                toastr.error('Terjadi kesalahan sistem. Coba lagi.');
            }
        });
    });
</script>

<script>
    // Format ribuan dengan titik (1.234.567)
    function formatThousandsID(nStr) {
        const digits = (nStr || '').toString().replace(/\D/g, '');
        if (!digits) return '';
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Hook untuk net_salary: ketik → auto format
    $(document).on('input', '#net_salary.money-separator', function() {
        const formatted = formatThousandsID($(this).val());
        $(this).val(formatted);
    });
</script>

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    // buka modal minta password saat klik ikon mata
    $(document).on('click', '.revealSalaryBtn', function() {
        const id = $(this).data('id');
        _pendingAction = 'reveal-salary';
        _pendingPayrollId = id;
        $('#salary_payroll_id').val(id);
        openPasswordModal();
    });

    // tutup modal
    $('#salaryModalCancel').on('click', function() {
        $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
    });
</script>
<script>
    function toggleContractTerm(statusVal) {
        if (statusVal === 'PKWT') {
            $('#contract_term_wrap').removeClass('hidden');
        } else {
            $('#contract_term_wrap').addClass('hidden');
            $('#contract_term').val('');
        }
    }

    // on-change handler
    $(document).on('change', '#employment_status', function() {
        toggleContractTerm(this.value);
    });
</script>

<script>
    // Kunci total field availability (tanpa mempengaruhi submit)
    (function lockAvailabilityField() {
        const $av = $('#availability_date');
        $av.on('keydown mousedown', function(e) {
            e.preventDefault();
        });
        $av.on('focus', function() {
            this.blur();
        });
    })();

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatDateInput(d) {
        return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    }

    function parseDateInput(val) {
        if (!val) return null;
        const [y, m, d] = val.split('-').map(Number);
        if (!y || !m || !d) return null;
        return new Date(y, m - 1, d);
    }

    function addMonthsKeepDay(date, months) {
        const y = date.getFullYear();
        const m = date.getMonth();
        const d = date.getDate();
        return new Date(y, m + months, d);
    }

    function computeAvailability() {
        const status = $('#employment_status').val();
        const term = parseInt($('#contract_term').val() || '0', 10);
        const startV = $('#work_start_date').val();
        const $av = $('#availability_date');

        if (status === 'PKWT' && term > 0 && startV) {
            const start = parseDateInput(startV);
            if (start) {
                const end = addMonthsKeepDay(start, term);
                $av.val(formatDateInput(end));
                return;
            }
        }
        $av.val('');
    }

    $(document).on('change', '#work_start_date', computeAvailability);
    $(document).on('change', '#contract_term', computeAvailability);

    const _origToggle = window.toggleContractTerm;
    window.toggleContractTerm = function(statusVal) {
        if (typeof _origToggle === 'function') _origToggle(statusVal);

        const $av = $('#availability_date');

        if (statusVal === 'PKWT') {
            $av.prop('required', true);
            computeAvailability();
        } else {
            $av.val('').prop('required', false);
        }
    };

    $(document).on('change', '#employment_status', function() {
        toggleContractTerm(this.value);
        computeAvailability();
    });

    $('#addPayrollBtn').on('click', function() {
        setTimeout(() => {
            toggleContractTerm($('#employment_status').val() || '');
            computeAvailability();
        }, 0);
    });
</script>
