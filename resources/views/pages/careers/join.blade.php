<div class="flex flex-wrap gap-6">
    <!-- Pakta Integritas -->
    <div class="min-w-[300px] flex-1 rounded-lg bg-white p-4 shadow-sm dark:bg-transparent">
        <div class="flex items-center justify-between">
            <h3 class="font text-lg text-gray-700 dark:text-white">Form Pakta Integritas</h3>
            <form id="integritasForm" class="flex-shrink-0">
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

    <!-- Surat Pernyataan -->
    <div class="min-w-[300px] flex-1 rounded-lg bg-white p-4 shadow-sm dark:bg-transparent">
        <div class="flex items-center justify-between">
            <h3 class="font text-lg text-gray-700 dark:text-white">Surat Pernyataan Penggunaan Fasilitas Elektronik</h3>
            <form id="pernyataanForm" class="flex-shrink-0">
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
<div class="mt-6 w-full rounded-xl bg-white p-6 dark:bg-gray-800" id="docid_onboarding"
    data-docid="{{ optional($onboarding)->docid }}">

    <header class="mb-6 flex items-center gap-2"> {{-- Section header with enhanced styling --}}
        <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">
            <span class="text-green-500">✅</span> Checklist Onboarding
        </h3>
    </header>

    <form id="checklistForm">
        @csrf
        <div id="checklistArea" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        </div>

        <div class="mt-8 flex justify-end">
            <button type="submit"
                class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-indigo-700 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Save Checklist
            </button>
        </div>
    </form>

    {{-- ===== Jadwal Onboarding (Form Terpisah) ===== --}}
    <form id="scheduleForm" class="mt-10 rounded-xl border border-gray-200 p-6 dark:border-gray-700">
    @csrf
    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
    <input type="hidden" name="jobapply_id"  value="{{ $career->docid ?? '' }}">

    <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-gray-100">Jadwal Onboarding</h3>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="flex flex-col">
        <label for="sch_availability_date" class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Kesediaan</label>
        <input type="date" id="sch_availability_date" name="availability_date"
                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" required>
        </div>
        <div class="flex flex-col">
        <label for="sch_work_start_date" class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai Kerja</label>
        <input type="date" id="sch_work_start_date" name="work_start_date"
                class="w-full rounded-lg border border-gray-300 p-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" required>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit" id="btnSaveSchedule"
                class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-2 text-base font-semibold text-white shadow-md transition-colors duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
        <span class="sch-text">Save Schedule & Send Email</span>
        <svg class="sch-spin ml-2 hidden h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        </button>
    </div>
    </form>


</div>



<script>
    $('#integritasForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('paktaintegritas.pdf') }}",
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
    $('#pernyataanForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);

        $.ajax({
            url: "{{ route('pernyataanelectonik.pdf') }}",
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
    $(document).ready(function() {
        const docidOnboarding = $('#docid_onboarding').data('docid');
        console.log('docid:', docidOnboarding);

        // Fetch data dari controller
        $.get(`/onboarding/checklist/${docidOnboarding}`, function(data) {
            let html = '';
            data.forEach(item => {
                const checked = item.checklist_onboarding_receive ? 'checked' : '';
                html += `
            <label>
              <input type="checkbox" name="checklist[]" value="${item.id}" ${checked}>
              ${item.checklist_onboarding_descr}
            </label>
          `;
            });
            $('#checklistArea').html(html);
        });

        // Submit perubahan
        $('#checklistForm').submit(function(e) {
            e.preventDefault();
            const checked = [];
            $('#checklistForm input[type=checkbox]:checked').each(function() {
                checked.push($(this).val());
            });

            $.post("{{ route('onboarding.checklist.update') }}", {
                _token: $('input[name="_token"]').val(),
                docid_onboarding: docidOnboarding,
                checked: checked
            }, function(response) {
                if (response.success) {
                    toastr.success('Checklist berhasil disimpan.');
                } else {
                    toastr.error('Gagal menyimpan checklist.');
                }
            }).fail(function() {
                toastr.error('Terjadi kesalahan saat menyimpan.');
            });
        });
    });
</script>

<script>
    $(function () {
    function setScheduleSaving(isSaving) {
        const $btn = $('#btnSaveSchedule');
        $btn.prop('disabled', isSaving);
        $btn.find('.sch-spin').toggleClass('hidden', !isSaving);
        $btn.find('.sch-text').text(isSaving ? 'Saving...' : 'Save Schedule');
    }

    $('#scheduleForm').on('submit', function (e) {
        e.preventDefault();

        const payload = {
            _token: $(this).find('input[name="_token"]').val(),
            applicant_id: $(this).find('input[name="applicant_id"]').val(),
            jobapply_id:  $(this).find('input[name="jobapply_id"]').val(),
            availability_date: $('#sch_availability_date').val(),
            work_start_date:   $('#sch_work_start_date').val()
        };

        if (!payload.availability_date || !payload.work_start_date) {
        toastr.error('Tanggal Kesediaan dan Tanggal Mulai Kerja wajib diisi.');
        return;
        }

        setScheduleSaving(true);

        $.ajax({
        url: "{{ route('onboarding.schedule.update') }}",
        type: 'POST',
        data: payload,
        headers: { 'Accept': 'application/json' }
        })
        .done(function (resp) {
        if (resp && resp.success) {
            toastr.success(resp.message || 'Jadwal berhasil disimpan & email terkirim.');
        } else {
            toastr.error(resp.message || 'Gagal menyimpan jadwal.');
        }
        })
        .fail(function (xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            const msgs = [];
            Object.values(xhr.responseJSON.errors).forEach(arr => arr[0] && msgs.push(arr[0]));
            toastr.error(msgs.join('<br>'), 'Validation Error', { escapeHtml: true });
        } else {
            toastr.error('Terjadi kesalahan saat menyimpan jadwal.');
        }
        })
        .always(function () {
        setScheduleSaving(false);
        });
    });
    });
</script>

