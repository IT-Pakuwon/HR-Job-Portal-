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
    <div class="bg-whitenrounded-lg space-y-4 shadow">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-white">Payroll Confirmation Data</h3>
            <button id="addPayrollBtn"
                class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fas fa-plus pr-2"></i>Add
            </button>
        </div>
        <div id="payrollModal"
            class="fixed inset-0 z-50 flex hidden items-center justify-center bg-gray-800/10 bg-opacity-50 transition duration-200">
            <div class="max-w-5xl rounded-md bg-white p-6">
                <h3 class="font-bold">Form Payroll</h3>
                <form id="payrollForm">
                    @csrf
                    <input type="hidden" name="jobapply_id" value="{{ $career->docid ?? '' }}">
                    <input type="hidden" name="jobid" value="{{ $career->docidposting ?? '' }}">
                    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
                    <input type="hidden" name="id" id="payroll_id">

                    <div class="flex w-full gap-8">
                        <div class="flex w-1/2 flex-col">
                            <label>Tanggungan</label>
                            <input type="text" name="tax_liability" class="mb-2 w-full border" required>
                        </div>

                        <div class="flex w-1/2 flex-col">
                            <label>NPWP</label>
                            <input type="text" name="npwp_id" class="mb-2 w-full border" required>
                        </div>
                    </div>

                    <div class="flex w-full gap-8">
                        <div class="flex w-1/2 flex-col">
                            <label>Rekening</label>
                            <input type="text" name="bank_account" class="mb-2 w-full border" required>
                        </div>

                        <div class="flex w-1/2 flex-col">
                            <label>Bank</label>
                            <input type="text" name="bank_name" class="mb-2 w-full border" required>
                        </div>
                    </div>

                    <div class="flex w-full gap-8">
                        <div class="flex w-1/2 flex-col">
                            <label>Salary</label>
                            <input type="number" name="net_salary" class="mb-2 w-full border" required>
                        </div>

                        <div class="flex w-1/2 flex-col">
                            <label>Fasilitas</label>
                            <input type="text" name="other_facility" class="mb-2 w-full border" required>
                        </div>
                    </div>

                    <div class="flex w-full gap-8">
                        <div class="flex w-1/2 flex-col">
                            <label>Tgl Kesediaan</label>
                            <input type="date" name="availability_date" class="mb-2 w-full border" required>
                        </div>

                        <div class="flex w-1/2 flex-col">
                            <label>Tgl Kerja</label>
                            <input type="date" name="work_start_date" class="mb-2 w-full border" required>
                        </div>
                    </div>

                    <label>Status Kepegawaian</label>
                    <input type="text" name="employment_status" class="mb-4 w-full border" required>

                    <div class="text-right">
                        <button type="submit" class="rounded bg-green-600 px-4 py-1 text-white">Simpan</button>
                        <button type="button" id="closeModal"
                            class="ml-2 rounded bg-gray-500 px-4 py-1 text-white">Batal</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Tabel Payroll -->
        <div class="overflow-x-auto">
            <table class="w-full border text-left text-sm" id="payrollTable">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-2">Tanggungan</th>
                        <th class="px-4 py-2">NPWP</th>
                        <th class="px-4 py-2">Rekening</th>
                        <th class="px-4 py-2">Bank</th>
                        <th class="px-4 py-2">Gaji</th>
                        <th class="px-4 py-2">Fasilitas</th>
                        <th class="px-4 py-2">Tgl Kesediaan</th>
                        <th class="px-4 py-2">Tgl Kerja</th>
                        <th class="px-4 py-2">Status Kepegawaian</th>
                        <th class="px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payrolls as $p)
                        <tr data-id="{{ $p->id }}" class="border-t">
                            <td class="px-4 py-2">{{ $p->tax_liability }}</td>
                            <td class="px-4 py-2">{{ $p->npwp_id }}</td>
                            <td class="px-4 py-2">{{ $p->bank_account }}</td>
                            <td class="px-4 py-2">{{ $p->bank_name }}</td>
                            <td class="px-4 py-2">{{ $p->net_salary }}</td>
                            <td class="px-4 py-2">{{ $p->other_facility }}</td>
                            <td class="px-4 py-2">{{ $p->availability_date }}</td>
                            <td class="px-4 py-2">{{ $p->work_start_date }}</td>
                            <td class="px-4 py-2">{{ $p->employment_status }}</td>
                            <td class="px-4 py-2">
                                <button
                                    class="editPayrollBtn rounded bg-blue-500 px-2 py-1 text-white hover:bg-blue-600">Edit</button>
                            </td>
                        </tr>
                    @endforeach
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

    // $('#payrollForm').submit(function(e) {
    //   e.preventDefault();
    //   let form = $(this);
    //   let url = form.find('#payroll_id').val() ? "{{ route('payrollconfirm.update') }}" : "{{ route('payrollconfirm.store') }}";

    //   $.post(url, form.serialize(), function(res) {
    //     location.reload();
    //   });
    // });
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

<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
