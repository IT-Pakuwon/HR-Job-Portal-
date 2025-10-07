<style>
    /* #payrollModal {
    backdrop-filter: blur(2px);
  } */
</style>

<!-- Wrapper untuk seluruh bagian payroll -->
<div class="space-y-6">

    <!-- Bagian PDF Preview -->
    <div class="flex flex-wrap gap-6">
        <!-- Payroll PDF -->
        <div class="min-w-[300px] flex-1 rounded-lg bg-white p-4 shadow-sm dark:bg-transparent">
            <div class="flex items-center justify-between">
                <h3 class="font text-lg text-gray-700 dark:text-white">Payroll Confirmation PDF</h3>
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
                        class="inline-flex items-center gap-2 rounded-md bg-gray-800 px-4 py-2 text-white transition hover:bg-gray-700">
                        <i data-lucide="eye" class="h-5 w-5"></i>
                        Preview
                    </button>
                </form>
            </div>
        </div>

        <!-- Offering Letter PDF -->
        <div class="min-w-[300px] flex-1 rounded-lg bg-white p-4 shadow-sm dark:bg-transparent">
            <div class="flex items-center justify-between">
                <h3 class="font text-lg text-gray-700 dark:text-white">Offering Letter PDF</h3>
                <form id="offeringForm" class="flex-shrink-0">
                    @csrf
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
                    <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
                    <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
                    <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-gray-800 px-4 py-2 text-white transition hover:bg-gray-700">
                        <i data-lucide="eye" class="h-5 w-5"></i>
                        Preview
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Payroll Section -->
    <div class="rounded-xl bg-white p-6 dark:bg-gray-800">

        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Payroll Confirmation Data</h3>
            <button id="addPayrollBtn"
                class="hover: inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <i class="fas fa-plus pr-2"></i>Add
            </button>
        </div>

        <div id="payrollModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 transition-opacity duration-300">
            <div class="w-full max-w-5xl rounded-xl bg-white p-8 dark:bg-gray-700">
                <h3 class="mb-6 text-2xl font-bold text-gray-800 dark:text-white">Form Payroll</h3>
                <form id="payrollForm">
                    @csrf
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="id" id="payroll_id">

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="flex flex-col">
                            <label for="tax_liability"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Dependants</label>
                            <select name="tax_liability" id="tax_liability"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                                <option value="">Select</option>
                                <option value="TK0">TK0</option>
                                <option value="K1">K1</option>
                                <option value="K2">K2</option>
                                <option value="K3">K3</option>
                            </select>
                        </div>

                        <div class="flex flex-col">
                            <label for="npwp_id"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">NPWP</label>
                            <input type="text" name="npwp_id" id="npwp_id"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <div class="flex flex-col">
                            <label for="bank_account"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Rekening</label>
                            <input type="text" name="bank_account" id="bank_account"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <div class="flex flex-col">
                            <label for="bank_name"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Bank</label>
                            <select name="bank_name" id="bank_name"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                                <option value="">-- Bank Name --</option>
                                <option value="BCA">BCA</option>
                                <option value="MANDIRI">MANDIRI</option>
                            </select>
                        </div>

                        <div class="flex flex-col">
                            <label for="net_salary"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Salary</label>
                            {{-- <input type="number" name="net_salary" id="net_salary"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required> --}}
                            <input type="text" name="net_salary" id="net_salary" inputmode="numeric"
                                class="money-separator w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                placeholder="0" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="other_facility"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Fasilitas</label>
                            <input type="text" name="other_facility" id="other_facility"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <div class="flex flex-col">
                            <label for="availability_date"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tgl
                                Kesediaan</label>
                            <input type="date" name="availability_date" id="availability_date"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <div class="flex flex-col">
                            <label for="work_start_date"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tgl
                                Kerja</label>
                            <input type="date" name="work_start_date" id="work_start_date"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col">
                        <label for="employment_status"
                            class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Status
                            Kepegawaian</label>
                        <select name="employment_status" id="employment_status"
                            class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required>
                            <option value="">-- Employment Status --</option>
                            <option value="PKWT">PKWT</option>
                            <option value="PKWTT">PKWTT</option>
                        </select>
                        <div id="contract_term_wrap" class="mt-4 hidden">
                            <label for="contract_term"
                                    class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Contract Term (PKWT)
                            </label>
                            <select name="contract_term" id="contract_term"
                                    class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                <option value="">-- Select Contract Term --</option>
                                <option value="2">Contract 2 bulan</option>
                                <option value="6">Contract 6 bulan</option>
                                <option value="12">Contract 12 bulan</option>
                            </select>
                        </div>
                    </div>
                    



                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit"
                            class="hover: inline-flex items-center rounded-lg bg-green-600 px-5 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Simpan</button>
                        <button type="button" id="closeModal"
                            class="hover: inline-flex items-center rounded-lg bg-gray-200 px-5 py-2 text-base font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Batal</button>
                    </div>
                </form>
            </div>
        </div>


        {{-- Payroll Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="payrollTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Tanggungan</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            NPWP</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Rekening</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Bank</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Gaji</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Fasilitas</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Tgl Kesediaan</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Tgl Kerja</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Status Kepegawaian</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($payrolls as $p)
                        <tr data-id="{{ $p->id }}"
                            class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ $p->tax_liability }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->npwp_id }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->bank_account }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->bank_name }}</td>
                            {{-- <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($p->net_salary, 0, ',', '.') }}</td> --}}
                                 <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="salary-mask" data-id="{{ $p->id }}">••••••</span>
                                    <button type="button"
                                        class="revealSalaryBtn inline-flex items-center rounded-md px-2 py-1 ml-2 text-xs bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500"
                                        data-id="{{ $p->id }}" aria-label="Reveal salary" title="Lihat gaji">
                                        <i data-lucide="eye" class="h-4 w-4"></i>
                                    </button>
                                </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->other_facility }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($p->availability_date)->format('d F Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($p->work_start_date)->format('d F Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->employment_status }} - Contract {{ $p->contract_term }} bulan</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <button
                                    class="editPayrollBtn inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10"
                                class="py-6 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                No payroll data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="rounded-xl bg-white p-6 dark:bg-gray-800">

        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">Payroll Sign</h3>
            <button id="addSignBtn"
                class="hover: inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                <i class="fas fa-plus pr-2"></i>Add Sign
            </button>
        </div>

        <div id="signModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4 transition-opacity duration-300">
            <div class="w-full max-w-5xl rounded-xl bg-white p-8 dark:bg-gray-700">
                <h3 class="mb-6 text-2xl font-bold text-gray-800 dark:text-white">Form Sign</h3>
                <form id="signForm">
                    @csrf
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="id" id="sign_id"><!-- tetap boleh, utk mode edit single -->

                    <!-- Container baris-baris -->
                    <div id="signRows" class="space-y-6"></div>

                    <!-- Tombol Add -->
                    <div class="mt-4">
                        <button type="button" id="addSignRow"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-indigo-700">
                            + Add Row
                        </button>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-green-600 px-5 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-green-700">
                            Simpan
                        </button>
                        <button type="button" id="closeModalsign"
                            class="inline-flex items-center rounded-lg bg-gray-200 px-5 py-2 text-base font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-300">
                            Batal
                        </button>
                    </div>
                </form>

                <!-- Template baris (tidak terlihat) -->
                <template id="signRowTemplate">
                    <div class="sign-row relative grid grid-cols-1 items-end gap-6 md:grid-cols-[120px_1fr_1fr]">
                        <!-- Urutan -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
                            <select name="aprvid[]"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                                <option value="" disabled selected>Urutan</option>
                                @for ($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Nama -->
                        <div class="flex flex-col">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                            <select name="aprvusername[]"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                required>
                                <option value="" disabled selected>-- Select Employee --</option>
                                @foreach ($userlist as $u)
                                    <option value="{{ $u->username }}" data-npk="{{ $u->name }}">
                                        {{ $u->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="aprvname[]" class="aprvname-input">
                        </div>

                        <!-- Jabatan -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                            <input type="text" name="jabatan[]"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <!-- Tombol hapus baris -->
                        <button type="button"
                            class="removeSignRow absolute -right-3 -top-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow hover:bg-red-700">
                            Hapus
                        </button>
                    </div>
                </template>



            </div>
        </div>


        {{-- Payroll Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="signTable">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Urutan</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Nama</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Jabatan</th>
                        <th
                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                    @forelse ($sign as $p)
                        <tr data-id="{{ $p->id }}"
                            class="transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ $p->aprvid }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->jabatan }}</td>
                            <td class="space-x-2 whitespace-nowrap px-6 py-4 text-sm">
                                <button
                                    class="editsignBtn inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Edit
                                </button>

                                <button
                                    class="deletesignBtn inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-200 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                    Delete
                                </button>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10"
                                class="py-6 text-center text-sm italic text-gray-500 dark:text-gray-400">
                                No Sign data found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="salaryPasswordModal"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 dark:bg-gray-700">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white"></h3>
                <form id="salaryPasswordForm">
                @csrf
                <input type="hidden" id="salary_payroll_id" name="payroll_id">
                <div class="mb-4">
                    <label class="mb-1 block text-sm text-gray-600 dark:text-gray-300">Password Anda</label>
                    <input type="password" id="salary_password" name="password" autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" id="salaryModalCancel"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-gray-800 hover:bg-gray-300 dark:bg-gray-600 dark:text-white">
                    Batal
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
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
  let _pendingAction   = null; // 'pdf-payroll' | 'pdf-offering' | 'edit-payroll' | 'reveal-salary'
  let _pendingFormEl   = null; // form element untuk PDF
  let _pendingPayrollId = null; // id payroll untuk edit/reveal

  function openPasswordModal() {
    // tampilkan modal password yang SUDAH ada: #salaryPasswordModal
    $('#salary_password').val('');
    $('#salaryPasswordModal').removeClass('hidden').addClass('flex');
    setTimeout(() => $('#salary_password').trigger('focus'), 0);
  }
</script>

<script>
    // $('#payrollpdf').on('submit', function(e) {
    //     e.preventDefault();
    //     var form = $(this);

    //     $.ajax({
    //         url: "{{ route('payrollconfirmation.pdf') }}",
    //         method: 'POST',
    //         data: form.serialize(),
    //         xhrFields: {
    //             responseType: 'blob'
    //         },
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         success: function(blob) {
    //             const url = window.URL.createObjectURL(blob);
    //             window.open(url, '_blank'); // 👈 preview PDF di tab baru
    //         },
    //         error: function() {
    //             alert("Failed to generate PDF.");
    //         }
    //     });
    // });
    $('#payrollpdf').on('submit', function(e){
    e.preventDefault();
    _pendingAction = 'pdf-payroll';
    _pendingFormEl = this;
    openPasswordModal();
    });

</script>

<script>
    // $('#offeringForm').on('submit', function(e) {
    //     e.preventDefault();
    //     var form = $(this);

    //     $.ajax({
    //         url: "{{ route('offeringletter.pdf') }}",
    //         method: 'POST',
    //         data: form.serialize(),
    //         xhrFields: {
    //             responseType: 'blob'
    //         },
    //         headers: {
    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //         },
    //         success: function(blob) {
    //             const url = window.URL.createObjectURL(blob);
    //             window.open(url, '_blank'); // 👈 preview PDF di tab baru
    //         },
    //         error: function() {
    //             alert("Failed to generate PDF.");
    //         }
    //     });
    // });
    $('#offeringForm').on('submit', function(e){
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

    // $('.editPayrollBtn').click(function() {
    //     var id = $(this).closest('tr').data('id');
    //     $.get('/payrollconfirm/' + id, function(data) {
    //         for (let key in data) {
    //             $('[name="' + key + '"]').val(data[key]);
    //         }
    //         // khusus net_salary → format tampilan
    //         if (typeof data.net_salary !== 'undefined' && data.net_salary !== null) {
    //             $('#net_salary').val(formatThousandsID(String(data.net_salary)));
    //         }
    //         $('#payroll_id').val(data.id);
    //         $('#payrollModal').removeClass('hidden');
    //     });
    // });

    // $('.editPayrollBtn').click(function () {
    //     const id = $(this).closest('tr').data('id');
    //     // simpan id sementara
    //     window._editingPayrollId = id;
    //     // buka modal password yang sama dengan “eye”
    //     $('#salary_payroll_id').val(id);
    //     $('#salary_password').val('');
    //     $('#salaryPasswordModal').removeClass('hidden').addClass('flex');

    //     // ganti submit modal agar dipakai untuk “edit preload”
    //     $('#salaryPasswordForm')
    //         .off('submit.editPreload')
    //         .on('submit.editPreload', function(e){
    //         e.preventDefault();
    //         const pwd = $('#salary_password').val();
    //         const pid = window._editingPayrollId;

    //         $.get('/payrollconfirm/' + pid, { password: pwd }, function(data){
    //             // isi form
    //             for (let key in data) {
    //             $('[name="'+key+'"]').val(data[key]);
    //             }
    //             if (data.net_salary != null) {
    //             $('#net_salary').val(formatThousandsID(String(data.net_salary)));
    //             }
    //             $('#payroll_id').val(data.id);
    //             $('#payrollModal').removeClass('hidden');
    //             // tutup modal password
    //             $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
    //         }).fail(function(xhr){
    //             if (xhr.status === 401) toastr.error('Password salah.');
    //             else if (xhr.status === 403) toastr.error('Anda tidak memiliki akses.');
    //             else toastr.error('Gagal mengambil data.');
    //         });
    //         });
    //     });

    $('.editPayrollBtn').click(function(){
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
    //Submit modal password
    // $('#passwordForm').on('submit', function(e){
    // e.preventDefault();
    // const pwd = $('#password_input').val();

    // if (_pendingAction === 'pdf-payroll') {
    //     sendPdfRequest("{{ route('payrollconfirmation.pdf') }}", _pendingFormEl, pwd);
    // }
    // else if (_pendingAction === 'pdf-offering') {
    //     sendPdfRequest("{{ route('offeringletter.pdf') }}", _pendingFormEl, pwd);
    // }
    // else if (_pendingAction === 'edit-payroll') {
    //     loadPayrollForEdit(_pendingPayrollId, pwd);
    // }
    // });
    // Submit modal password (SATU untuk semua aksi)
// sekali saja di atas:
$('#salaryPasswordForm')
  .off('submit.payroll')
  .on('submit.payroll', function (e) {
    e.preventDefault();
    const pwd = $('#salary_password').val();

    if (_pendingAction === 'pdf-payroll') {
      sendPdfRequest("{{ route('payrollconfirmation.pdf') }}", _pendingFormEl, pwd);
    } else if (_pendingAction === 'pdf-offering') {
      sendPdfRequest("{{ route('offeringletter.pdf') }}", _pendingFormEl, pwd);
    } else if (_pendingAction === 'edit-payroll') {
      loadPayrollForEdit(_pendingPayrollId, pwd);
    } else if (_pendingAction === 'reveal-salary') {
      // panggil reveal di sini (seperti yang sudah kamu punya)
      $.post("{{ route('payrollconfirm.reveal') }}", {
        _token: $('meta[name="csrf-token"]').attr('content'),
        payroll_id: _pendingPayrollId,
        password: pwd
      })
      .done(function(resp){
        if (resp && resp.success) {
          const formatted = formatThousandsID(String(resp.salary));
          $('.salary-mask[data-id="'+_pendingPayrollId+'"]').text(formatted);
          $('.revealSalaryBtn[data-id="'+_pendingPayrollId+'"] i[data-lucide]')
            .attr('data-lucide','eye-off');
          if (window.lucide) lucide.createIcons();
          toastr.success('Gaji ditampilkan.');
          $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
        } else {
          toastr.error(resp?.message || 'Gagal verifikasi.');
        }
      })
      .fail(function(xhr){
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
    url, method:'POST', data: fd,
    processData: false, contentType: false,
    xhrFields: { responseType:'blob' },
    success: function(blob, status, xhr){
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
    error: function(){
      toastr.error('Verifikasi gagal');
    }
  });
}

function loadPayrollForEdit(id, pwd){
  $.get('/payrollconfirm/'+id, { password: pwd }, function(data){
    for (let key in data) {
      $('[name="'+key+'"]').val(data[key]);
    }
    if (data.net_salary != null) {
      $('#net_salary').val(formatThousandsID(String(data.net_salary)));
    } else {
      $('#net_salary').val('');
    }
    $('#payroll_id').val(data.id);
    $('#payrollModal').removeClass('hidden');
    $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
  }).fail(function(xhr){
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
            $('[name="aprvusername"]').val(data
                .name); // atau data.aprvusername sesuai field yang dikirim
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

            // kalau sedang submit, abaikan
            if (submittingSign) return;
            submittingSign = true;

            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const btnHtml = $btn.html(); // simpan isi tombol

            // Kunci tombol
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
                        // tidak perlu re-enable; kita reload
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
                    // Kalau tidak reload (karena error), kembalikan tombol & guard
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

        // Init: jaga2 kalau user buka modal pertama kali
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
            $(this).parent(); // fallback jika bukan repeatable

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
            url: '/signconfirm/' + id, // sesuaikan bila pakai route name
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(resp) {
                if (resp && resp.success) {
                    toastr.success('Data sign berhasil dihapus.');
                    $tr.remove();

                    // Jika kosong, tampilkan row "No Sign data found."
                    if ($('#signTable tbody tr').length === 0) {
                        $('#signTable tbody').html(`
              <tr>
                <td colspan="10" class="py-6 text-center text-sm italic text-gray-500 dark:text-gray-400">
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
        // Ambil hanya digit
        const digits = (nStr || '').toString().replace(/\D/g, '');
        if (!digits) return '';
        // Sisipkan titik per 3 digit
        return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Hook untuk net_salary: ketik → auto format
    $(document).on('input', '#net_salary.money-separator', function() {
        const caretToEnd = document.activeElement === this; // caret akan ke akhir; cukup oke
        const formatted = formatThousandsID($(this).val());
        $(this).val(formatted);
    });
</script>

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
  // buka modal minta password
//   $(document).on('click', '.revealSalaryBtn', function () {
//       const id = $(this).data('id');
//       $('#salary_payroll_id').val(id);
//       $('#salary_password').val('');
//       $('#salaryPasswordModal').removeClass('hidden').addClass('flex');
//       setTimeout(() => $('#salary_password').trigger('focus'), 0);
//   });
// buka modal minta password saat klik ikon mata
$(document).on('click', '.revealSalaryBtn', function () {
  const id = $(this).data('id');
  _pendingAction = 'reveal-salary';
  _pendingPayrollId = id;
  $('#salary_payroll_id').val(id); // boleh tetap diisi kalau butuh
  openPasswordModal();
});


  // tutup modal
  $('#salaryModalCancel').on('click', function () {
      $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
  });

  // submit verifikasi
//   $('#salaryPasswordForm').on('submit', function (e) {
//       e.preventDefault();

//       const payload = $(this).serialize(); // payroll_id + password + _token
//       const payrollId = $('#salary_payroll_id').val();

//       $.ajax({
//           type: 'POST',
//           url: "{{ route('payrollconfirm.reveal') }}",
//           data: payload,
//           success: function (resp) {
//               if (resp && resp.success) {
//                   const formatted = formatThousandsID(String(resp.salary));
//                   const $cellSpan = $('.salary-mask[data-id="' + payrollId + '"]');
//                   $cellSpan.text(formatted);
//                   // opsional: ganti ikon jadi eye-off
//                   const $btn = $('.revealSalaryBtn[data-id="' + payrollId + '"] i[data-lucide]');
//                   $btn.attr('data-lucide', 'eye-off');
//                   if (window.lucide) { lucide.createIcons(); }

//                   toastr.success('Gaji ditampilkan.');
//                   $('#salaryPasswordModal').addClass('hidden').removeClass('flex');
//               } else {
//                   toastr.error(resp?.message || 'Gagal verifikasi.');
//               }
//           },
//           error: function (xhr) {
//               if (xhr.status === 401) {
//                   toastr.error('Password salah.');
//               } else if (xhr.status === 403) {
//                   toastr.error('Anda tidak memiliki akses.');
//               } else {
//                   toastr.error('Terjadi kesalahan. Coba lagi.');
//               }
//           }
//       });
//   });
</script>
<script>
    function toggleContractTerm(statusVal) {
  if (statusVal === 'PKWT') {
    $('#contract_term_wrap').removeClass('hidden');
  } else {
    $('#contract_term_wrap').addClass('hidden');
    $('#contract_term').val(''); // bersihkan jika bukan PKWT
  }
}

// on-change handler
$(document).on('change', '#employment_status', function () {
  toggleContractTerm(this.value);
});

</script>

