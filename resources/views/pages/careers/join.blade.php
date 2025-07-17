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
<!-- Checklist Onboarding -->
<div class="mt-6 w-full rounded-lg bg-white p-4 shadow-sm dark:bg-transparent" id="docid_onboarding"
    data-docid="{{ optional($onboarding)->docid }}">
    <h3 class="mb-4 text-lg font-semibold text-gray-700">Checklist Onboarding</h3>

    {{-- <form id="checklistForm">
    @csrf
    <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">

    <div class="grid grid-cols-2 gap-4">
      <label><input type="checkbox" name="checklist[]" value="On Boarding Video"> On Boarding Video</label>
      <label><input type="checkbox" name="checklist[]" value="Integritas"> Integritas</label>
      <label><input type="checkbox" name="checklist[]" value="Komputer/Laptop"> Komputer/Laptop</label>
      <label><input type="checkbox" name="checklist[]" value="BPJS Tenaga Kerja"> BPJS Tenaga Kerja</label>
      <label><input type="checkbox" name="checklist[]" value="BPJS Kesehatan"> BPJS Kesehatan</label>
      <label><input type="checkbox" name="checklist[]" value="Pembuatan Talenta"> Pembuatan Talenta</label>
      <label><input type="checkbox" name="checklist[]" value="Email"> Email</label>
      <label><input type="checkbox" name="checklist[]" value="Parkir"> Parkir</label>
      <label><input type="checkbox" name="checklist[]" value="Pengenalan Akses"> Pengenalan Akses</label>
    </div>

    <button type="submit" class="mt-4 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-500 transition">
      Simpan Checklist
    </button>
  </form> --}}

    <form id="checklistForm">
        @csrf
        {{-- <input type="hidden" id="docid_onboarding" value="{{ $onboarding->docid ?? '' }}"> --}}
        <div id="checklistArea" class="grid grid-cols-2 gap-4">
            <!-- Isi akan dimuat dengan JS -->
        </div>

        <button type="submit"
            class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Save
        </button>
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
