<style>
  #payrollModal {
    backdrop-filter: blur(2px);
  }
</style>

<!-- Wrapper untuk seluruh bagian payroll -->
<div class="mt-8 space-y-6">

  <!-- Bagian PDF Preview -->
  <div class="flex flex-wrap gap-6">
    <!-- Payroll PDF -->
    <div class="flex-1 min-w-[300px] bg-white shadow-sm rounded-lg p-4">
      <div class="flex items-center justify-between">
        <h5 class="text-gray-800 font-medium">Payroll Confirmation PDF</h5>
        <form id="payrollpdf" class="flex-shrink-0">
          @csrf
          <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
          <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
          <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
          <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
          <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
          <input type="hidden" name="refid" value="{{ $career->refid ?? '' }}">
          <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
            <i data-lucide="eye" class="w-5 h-5"></i>
            Preview
          </button>
        </form>
      </div>
    </div>

    <!-- Offering Letter PDF -->
    <div class="flex-1 min-w-[300px] bg-white shadow-sm rounded-lg p-4">
      <div class="flex items-center justify-between">
        <h5 class="text-gray-800 font-medium">Offering Letter PDF</h5>
        <form id="offeringForm" class="flex-shrink-0">
          @csrf
          <input type="hidden" name="applicant_id" value="{{ $applicant->applicant_id ?? '' }}">
          <input type="hidden" name="job_title" value="{{ $career->job_title ?? '' }}">
          <input type="hidden" name="cpnyid" value="{{ $career->cpnyid ?? '' }}">
          <input type="hidden" name="departementid" value="{{ $career->departementid ?? '' }}">
          <input type="hidden" name="job_level" value="{{ $career->job_level ?? '' }}">
          <button type="submit" class="inline-flex items-center gap-2 bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition">
            <i data-lucide="eye" class="w-5 h-5"></i>
            Preview
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Payroll Section -->
  <div class="bg-white p-6 rounded-lg shadow space-y-4">
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-700">Payroll Confirmation Data</h3>
      {{-- <button id="addPayrollBtn" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition">
        + Tambah
      </button> --}}
      <button type="button" onclick="openAgendaModal()" style="margin-bottom: 10px; padding: 8px 16px; background-color: #6366f1; color: white; border: none; border-radius: 5px;">
        Create Schedule
      </button>
    </div>
    {{-- <div id="payrollModal" class="hidden fixed inset-0 z-50 bg-gray-800 bg-opacity-50 flex justify-center items-center">
      <div class="bg-white p-6 rounded-md w-[500px]  ">
        <h3 class="font-bold mb-4">Form Payroll</h3>
        <form id="payrollForm">
          @csrf
          <input type="hidden" name="id" id="payroll_id">
          <!-- Form fields here... -->
          <div class="text-right mt-4">
            <button type="submit" class="bg-green-600 text-white px-4 py-1 rounded">Simpan</button>
            <button type="button" id="closeModal" class="bg-gray-500 text-white px-4 py-1 rounded ml-2">Batal</button>
          </div>
        </form>
      </div>
    </div> --}}

    <div id="agendaModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
  <div style="background-color: white; margin: 10% auto; padding: 20px; width: 400px; border-radius: 10px;">
    <h3>Create Schedule</h3>
    <hr><br>
    <form id="createAgendaForm">
      @csrf
      <input type="hidden" name="refid" value="{{ $career->docid }}" />
      <input type="hidden" name="cpnyid" value="{{ $jobposting->cpnyid }}" />
      <input type="hidden" name="departementid" value="{{ $jobposting->departementid }}" />

      <label>Title</label>
      <input type="text" name="title" required class="form-control" style="width: 100%; margin-bottom: 10px;" />

      <label>Description</label>
      <textarea name="description" required class="form-control" style="width: 100%; margin-bottom: 10px;"></textarea>

      <label>Start Date</label>
      <input type="datetime-local" name="startdate" required class="form-control" style="width: 100%; margin-bottom: 10px;" />

      <label>End Date</label>
      <input type="datetime-local" name="enddate" required class="form-control" style="width: 100%; margin-bottom: 10px;" />

      <label>Type</label>
      <select class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800 " name="reftype" style="width: 100%; margin-bottom: 16px;" required>
        @foreach($typestep as $p)
            <option value="{{ $p->step_id }}">{{ $p->step_descr }}</option>
        @endforeach
      </select>

      <label>Location</label>
      <input type="text" name="location" required class="form-control" style="width: 100%; margin-bottom: 10px;" />

      <label>Address</label>
      <textarea name="location_address" required class="form-control" style="width: 100%; margin-bottom: 10px;"></textarea>

      <label>Participant</label>
      <select class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800 select2" name="participant[]" multiple style="width: 100%; margin-bottom: 16px;" required>
        @foreach($userlist as $p)
            <option value="{{ $p->username }}">{{ $p->name }}</option>
        @endforeach
      </select>

      <div style="margin-top: 10px;">
        <button type="submit" style="background-color: #22c55e; color: white; padding: 6px 12px; border: none; border-radius: 5px;">Save</button>
        <button type="button" onclick="closeAgendaModal()" style="background-color: #e5e7eb; color: #374151; padding: 6px 12px; border: none; border-radius: 5px; margin-left: 10px;">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>
<div id="cancelModal" style="display: none; position: fixed; z-index: 99999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
  <div style="background: white; padding: 20px; width: 400px; margin: 15% auto; border-radius: 10px;">
    <h3>Cancel Schedule</h3>
    <form id="cancelForm">
      @csrf
      <input type="hidden" name="agenda_id" id="cancel_agenda_id">
      <label>Reason for Cancellation</label>
      <textarea name="reason" required style="width: 100%; margin: 10px 0;"></textarea>
      <button type="submit" style="background-color: #ef4444; color: white; padding: 6px 12px; border: none; border-radius: 5px;">Submit</button>
      <button type="button" onclick="closeCancelModal()" style="margin-left: 10px;">Close</button>
    </form>
  </div>
</div>



    <!-- Tabel Payroll -->
    <div class="overflow-x-auto">
      <table class="w-full border text-sm text-left" id="payrollTable">
        <thead class="bg-gray-200 text-gray-700">
          <tr>
            <th class="px-4 py-2">Applicant ID</th>
            <th class="px-4 py-2">NPWP</th>
            <th class="px-4 py-2">Rekening</th>
            <th class="px-4 py-2">Bank</th>
            <th class="px-4 py-2">Gaji</th>
            <th class="px-4 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($payrolls as $p)
          <tr data-id="{{ $p->id }}" class="border-t">
            <td class="px-4 py-2">{{ $p->applicant_id }}</td>
            <td class="px-4 py-2">{{ $p->npwp }}</td>
            <td class="px-4 py-2">{{ $p->rekening }}</td>
            <td class="px-4 py-2">{{ $p->bank }}</td>
            <td class="px-4 py-2">{{ $p->salary }}</td>
            <td class="px-4 py-2">
              <button class="editPayrollBtn bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600">Edit</button>
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
          $('[name="'+key+'"]').val(data[key]);
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
      let url = form.find('#payroll_id').val() ? "{{ route('payrollconfirm.update') }}" : "{{ route('payrollconfirm.store') }}";

      $.post(url, form.serialize(), function(res) {
        location.reload();
      });
    });
</script>

<script>
  function openAgendaModal() {
    document.getElementById('agendaModal').style.display = 'block';
  }

  function closeAgendaModal() {
    document.getElementById('agendaModal').style.display = 'none';
  }

  $('#createAgendaForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '{{ route("agendas.store") }}',
      method: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        // alert('Agenda berhasil dibuat!');
        toastr.success("Schedule created successfully");
        location.reload();
      },
      error: function(xhr) {
        alert('Gagal membuat agenda: ' + xhr.responseText);
      }
    });
  });
</script>

<script>
  function openCancelModal(id) {
    $('#cancel_agenda_id').val(id);
    $('#cancelModal').show();
  }
  
  function closeCancelModal() {
    $('#cancelModal').hide();
  }
  
  $('#cancelForm').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
      url: '{{ route('agendas.cancel') }}',
      type: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        toastr.success("Schedule cancelled successfully");
        location.reload();
      },
      error: function(xhr) {
        alert('Cancel failed: ' + xhr.responseText);
      }
    });
  });
</script>

<script>
  $(document).ready(function () {
    $('.select2').select2({
        placeholder: "Select Participants",        
        allowClear: true,
        width: '100%',
        dropdownParent: $('#agendaModal') // ⬅️ ini penting!
    });
  });
</script>

