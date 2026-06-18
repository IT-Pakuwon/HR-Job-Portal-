<div class="space-y-3">

    {{-- ── Documents ────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">Documents</p>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700/60">
            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Payroll Confirmation PDF</p>
                <form id="payrollpdf" class="flex-shrink-0">
                    @csrf
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                    <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                    <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                    <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                    <input type="hidden" name="refid" value="{{ $career->refid ?? '' }}">
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                        Preview
                    </button>
                </form>
            </div>
            <div class="flex items-center justify-between px-5 py-3">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Offering Letter PDF</p>
                <form id="offeringForm" class="flex-shrink-0">
                    @csrf
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                    <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                    <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                    <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                        Preview
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Payroll Confirmation Data ────────────────────────────── --}}
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">

        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Payroll Confirmation Data</p>
            <button id="addPayrollBtn"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add
            </button>
        </div>

        {{-- Payroll Modal --}}
        <div id="payrollModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-5xl rounded-lg bg-white p-6 dark:bg-gray-800">
                <h3 class="mb-5 text-sm font-bold text-gray-800 dark:text-white">Form Payroll</h3>
                <form id="payrollForm">
                    @csrf
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="id" id="payroll_id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="flex flex-col">
                            <label for="tax_liability" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Dependants</label>
                            <select name="tax_liability" id="tax_liability"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="">Select</option>
                                <option value="TK0">TK0</option>
                                <option value="K1">K1</option>
                                <option value="K2">K2</option>
                                <option value="K3">K3</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="npwp_id" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">NPWP</label>
                            <input type="text" name="npwp_id" id="npwp_id"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                        <div class="flex flex-col">
                            <label for="bank_account" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Rekening</label>
                            <input type="text" name="bank_account" id="bank_account"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                        <div class="flex flex-col">
                            <label for="bank_name" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Bank</label>
                            <select name="bank_name" id="bank_name"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="">Bank Name</option>
                                <option value="BCA">BCA</option>
                                <option value="MANDIRI">MANDIRI</option>
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label for="net_salary" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Salary</label>
                            <input type="text" name="net_salary" id="net_salary" inputmode="numeric"
                                class="money-separator w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="0" required>
                        </div>
                        <div class="flex flex-col">
                            <label for="other_facility" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Fasilitas</label>
                            <input type="text" name="other_facility" id="other_facility"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                        <div class="flex flex-col">
                            <label for="work_start_date" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Tgl Masuk Kerja</label>
                            <input type="date" name="work_start_date" id="work_start_date"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                        <div class="flex flex-col">
                            <label for="availability_date" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Tgl Selesai Kerja</label>
                            <input type="date" name="availability_date" id="availability_date"
                                class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                readonly>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col">
                        <label for="employment_status" class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Status Kepegawaian</label>
                        <select name="employment_status" id="employment_status"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Employment Status</option>
                            <option value="PKWT">PKWT</option>
                            <option value="PKWTT">PKWTT</option>
                        </select>
                        <div id="contract_term_wrap" class="mt-3 hidden">
                            <label for="contract_term" class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Contract Term (PKWT)</label>
                            <select name="contract_term" id="contract_term"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Select Contract Term</option>
                                <option value="2">Contract 2 bulan</option>
                                <option value="3">Contract 3 bulan</option>
                                <option value="6">Contract 6 bulan</option>
                                <option value="12">Contract 12 bulan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" id="closeModal"
                            class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Payroll Table --}}
        <div class="overflow-x-auto">
            <table class="w-full" id="payrollTable">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60">
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tanggungan</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">NPWP</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Rekening</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Bank</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Gaji</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Fasilitas</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tgl Masuk</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Tgl Selesai</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Status</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    @forelse ($payrolls as $p)
                        <tr data-id="{{ $p->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs font-semibold text-gray-800 dark:text-gray-100">{{ $p->tax_liability }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->npwp_id }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->bank_account }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->bank_name }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">
                                <div class="flex items-center gap-1.5">
                                    <span class="salary-mask tabular-nums" data-id="{{ $p->id }}">••••••</span>
                                    <button type="button"
                                        class="revealSalaryBtn rounded bg-gray-100 px-1.5 py-0.5 text-gray-500 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:hover:bg-gray-600"
                                        data-id="{{ $p->id }}" aria-label="Reveal salary">
                                        <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->other_facility }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($p->work_start_date)->translatedFormat('d F Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ \Carbon\Carbon::parse($p->availability_date)->translatedFormat('d F Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->employment_status }} - Contract {{ $p->contract_term }} bulan</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs">
                                <button class="editPayrollBtn inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-gray-400 hover:text-gray-800 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-6 text-center text-xs italic text-gray-400 dark:text-gray-500">No payroll data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Payroll Sign ──────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800">

        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-gray-700/60">
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Payroll Sign</p>
            <button id="addSignBtn"
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Add Sign
            </button>
        </div>

        {{-- Sign Modal --}}
        <div id="signModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-4xl rounded-lg bg-white p-6 dark:bg-gray-800">
                <h3 class="mb-5 text-sm font-bold text-gray-800 dark:text-white">Form Sign</h3>
                <form id="signForm">
                    @csrf
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="id" id="sign_id">

                    <div id="signRows" class="space-y-4"></div>

                    <div class="mt-4">
                        <button type="button" id="addSignRow"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-gray-400 focus:outline-none dark:border-gray-600 dark:text-gray-400">
                            + Add Row
                        </button>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" id="closeModalsign"
                            class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                            Simpan
                        </button>
                    </div>
                </form>

                <template id="signRowTemplate">
                    <div class="sign-row relative grid grid-cols-1 items-end gap-4 md:grid-cols-[120px_1fr_1fr]">
                        <div class="flex flex-col">
                            <label class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Urutan</label>
                            <select name="aprvid[]"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="" disabled selected>Urutan</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Nama</label>
                            <select name="aprvusername[]"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                                <option value="" disabled selected>Select Employee</option>
                                @foreach ($userlist as $u)
                                    <option value="{{ $u->username }}" data-npk="{{ $u->name }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="aprvname[]" class="aprvname-input">
                        </div>
                        <div class="flex flex-col">
                            <label class="mb-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Jabatan</label>
                            <input type="text" name="jabatan[]"
                                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                required>
                        </div>
                        <button type="button"
                            class="removeSignRow absolute -right-3 -top-3 rounded-full bg-red-500 px-2 py-0.5 text-xs font-semibold text-white hover:bg-red-600">
                            ×
                        </button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Sign Table --}}
        <div class="overflow-x-auto">
            <table class="w-full" id="signTable">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60">
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Urutan</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Nama</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Jabatan</th>
                        <th class="px-4 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                    @forelse ($sign as $p)
                        <tr data-id="{{ $p->id }}" class="hover:bg-gray-50 dark:hover:bg-gray-700/20">
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs font-semibold text-gray-800 dark:text-gray-100">{{ $p->aprvid }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->name }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs text-gray-600 dark:text-gray-300">{{ $p->jabatan }}</td>
                            <td class="whitespace-nowrap px-4 py-2.5 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <button class="editsignBtn inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:border-gray-400 hover:text-gray-800 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400">Edit</button>
                                    <button class="deletesignBtn inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-500 transition hover:bg-red-50 focus:outline-none active:scale-95 dark:border-red-800/40 dark:text-red-400">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-xs italic text-gray-400 dark:text-gray-500">No Sign data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Password Verify Modal --}}
        <div id="salaryPasswordModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-sm rounded-lg bg-white p-5 dark:bg-gray-800">
                <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">Verifikasi Password</h3>
                <form id="salaryPasswordForm">
                    @csrf
                    <input type="hidden" id="salary_payroll_id" name="payroll_id">
                    <div class="mb-4">
                        <label class="mb-1 block text-xs font-semibold text-gray-500 dark:text-gray-400">Password Anda</label>
                        <input type="password" id="salary_password" name="password" autocomplete="current-password"
                            class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-gray-400 focus:bg-white focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" id="salaryModalCancel"
                            class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                            Verifikasi
                        </button>
                    </div>
                </form>
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
        _pendingPayrollId = $(this).closest('tr').data('id');
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
            error: function() {
                toastr.error('Verifikasi gagal');
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
        var id = $(this).closest('tr').data('id');
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

        function addRow() {
            const node = tpl.content.cloneNode(true);
            rows.append(node);
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
            $(this).closest('.sign-row').remove();
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
            const id = $(this).closest('tr').data('id');
            $.get('/signconfirm/' + id, function(data) {
                rows.empty();
                addRow();
                const row = rows.find('.sign-row').first();
                row.find('[name="aprvid[]"]').val(data.aprvid);
                row.find('[name="aprvusername[]"]').val(data.aprvusername ?? data.aprvusername);
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
        const $tr = $(this).closest('tr');
        const id = $tr.data('id');

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
                    $tr.remove();

                    if ($('#signTable tbody tr').length === 0) {
                        $('#signTable tbody').html(`
              <tr>
                <td colspan="4" class="py-6 text-center text-xs italic text-gray-400 dark:text-gray-500">
                  No Sign data found.
                </td>
              </tr>
            `);
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
