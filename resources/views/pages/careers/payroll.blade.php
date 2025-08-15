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
                class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggungan</label>
                            <input type="text" name="tax_liability" id="tax_liability"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
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
                            <input type="text" name="bank_name" id="bank_name"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
                        </div>

                        <div class="flex flex-col">
                            <label for="net_salary"
                                class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Salary</label>
                            <input type="number" name="net_salary" id="net_salary"
                                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                required>
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
                        <input type="text" name="employment_status" id="employment_status"
                            class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            required>
                    </div>


                    <div class="mt-8 flex justify-end gap-3">
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-green-600 px-5 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-green-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">Simpan</button>
                        <button type="button" id="closeModal"
                            class="inline-flex items-center rounded-lg bg-gray-200 px-5 py-2 text-base font-semibold text-gray-700 shadow-md transition-colors duration-200 hover:bg-gray-300 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500 dark:focus:ring-offset-gray-800">Batal</button>
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
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ number_format($p->net_salary, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->other_facility }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($p->availability_date)->format('d F Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($p->work_start_date)->format('d F Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                {{ $p->employment_status }}</td>
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
                class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
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
                            class="inline-flex items-center rounded-lg bg-green-600 px-5 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-green-700">
                            Simpan
                        </button>
                        <button type="button" id="closeModalsign"
                            class="inline-flex items-center rounded-lg bg-gray-200 px-5 py-2 text-base font-semibold text-gray-700 shadow-md transition-colors duration-200 hover:bg-gray-300">
                            Batal
                        </button>
                    </div>
                </form>

                <!-- Template baris (tidak terlihat) -->
                <template id="signRowTemplate">
                <div class="sign-row grid gap-6 grid-cols-1 md:grid-cols-[120px_1fr_1fr] items-end relative">
                    <!-- Urutan -->
                    <div class="flex flex-col">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Urutan</label>
                    <select name="aprvid[]" class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" required>
                        <option value="" disabled selected>Urutan</option>
                        @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                    </div>

                    <!-- Nama -->
                    <div class="flex flex-col">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama</label>
                    <select name="aprvusername[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                        <option value="" disabled selected>-- Select Employee --</option>
                        @foreach ($userlist as $u)
                        <option value="{{ $u->username }}" data-npk="{{ $u->name }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="aprvname[]" class="aprvname-input">
                    </div>

                    <!-- Jabatan -->
                    <div class="flex flex-col">
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Jabatan</label>
                    <input type="text" name="jabatan[]" class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" required>
                    </div>

                    <!-- Tombol hapus baris -->
                    <button type="button"
                    class="removeSignRow absolute -top-3 -right-3 rounded-full bg-red-600 px-2.5 py-1 text-xs font-semibold text-white shadow hover:bg-red-700">
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
                            <td class="whitespace-nowrap px-6 py-4 text-sm space-x-2">
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
    </div>
</div>
<script>
    $('#payrollpdf').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('payrollconfirmation.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>

<script>
    $('#offeringForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('offeringletter.pdf') }}",
            method: 'POST',
            data: form.serialize(),
            xhrFields: {
                responseType: 'blob'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(blob) {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // 👈 preview PDF di tab baru
            },
            error: function() {
                alert("Failed to generate PDF.");
            }
        });
    });
</script>
<script>
    $('#addPayrollBtn').click(function() {
        $('#payrollForm')[0].reset();
        $('#payroll_id').val('');
        $('#payrollModal').removeClass('hidden');
    });

    $('.editPayrollBtn').click(function() {
        var id = $(this).closest('tr').data('id');
        $.get('/payrollconfirm/' + id, function(data) {
            for (let key in data) {
                $('[name="' + key + '"]').val(data[key]);
            }
            $('#payroll_id').val(data.id);
            $('#payrollModal').removeClass('hidden');
        });
    });

    $('#closeModal').click(function() {
        $('#payrollModal').addClass('hidden');
    });

    $('#payrollForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
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
            }
        });
    });
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
            $('[name="aprvusername"]').val(data.name); // atau data.aprvusername sesuai field yang dikirim
            $('[name="jabatan"]').val(data.jabatan);
            $('#sign_id').val(data.id);
            $('#signModal').removeClass('hidden');
        });
    });


    $('#closeModalsign').click(function() {
        $('#signModal').addClass('hidden');
    });

    $('#signForm').submit(function(e) {
        e.preventDefault();
        let form = $(this);
        let url = form.find('#sign_id').val() ?
            "{{ route('signconfirm.update') }}" :
            "{{ route('signconfirm.store') }}";

        $.ajax({
            type: 'POST',
            url: url,
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success('Data sign berhasil disimpan.');
                    setTimeout(() => location.reload(), 1000); // reload setelah toastr tampil
                } else {
                    toastr.error('Gagal menyimpan data sign.');
                }
            },
            error: function(xhr) {
                if (xhr.status === 409 && xhr.responseJSON?.message) {
                    toastr.warning(xhr.responseJSON
                        .message); // pesan duplikat sign atau onboarding
                } else {
                    toastr.error('Terjadi kesalahan sistem. Coba lagi.');
                }
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
  $(document).on('change', 'select[name="aprvusername[]"]', function () {
    const opt  = $(this).find('option:selected');
    const name = opt.data('name') ?? opt.text();    

    const row = $(this).closest('.sign-row').length 
                ? $(this).closest('.sign-row') 
                : $(this).parent(); // fallback jika bukan repeatable

    row.find('input.aprvname-input').val(name);

  });

  // opsional: saat buka modal, trigger change agar hidden terisi kalau ada default value
  $('#addSignBtn').on('click', function() {
    $('select[name="aprvusername[]"]').trigger('change');
  });
</script>

<script>
  // Hapus row Sign
  $(document).on('click', '.deletesignBtn', function () {
    const $tr = $(this).closest('tr');
    const id  = $tr.data('id');

    if (!confirm('Hapus data sign ini?')) return;

    $.ajax({
      url: '/signconfirm/' + id,       // sesuaikan bila pakai route name
      type: 'DELETE',
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
      success: function (resp) {
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
      error: function (xhr) {
        toastr.error('Terjadi kesalahan sistem. Coba lagi.');
      }
    });
  });
</script>




<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
