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
