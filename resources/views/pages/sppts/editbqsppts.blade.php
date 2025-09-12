<x-app-layout>
   
    <style>
        /* Overlay full-screen */
        #loadingSpinnerContainer{
            position: fixed;
            inset: 0;
            display: none;                 /* akan ditampilkan via JS */
            background: rgba(17,24,39,.55);
            backdrop-filter: blur(2px);
            z-index: 2000;
        }

        /* Kartu spinner di tengah */
        #loadingSpinnerContainer .loading-card{
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            padding: 18px 22px;
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(31,41,55,.9), rgba(17,24,39,.9));
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 10px 30px rgba(0,0,0,.35), inset 0 0 0 1px rgba(255,255,255,.04);
        }

        /* Spinner dual ring */
        #loadingSpinnerContainer .loading-spinner{
            width: 54px; height: 54px; border-radius: 50%;
            border: 4px solid transparent; border-top-color: #6366f1; /* indigo-500 */
            animation: spin 1s linear infinite; position: relative;
        }
        #loadingSpinnerContainer .loading-spinner::after{
            content: ""; position: absolute; inset: 6px; border-radius: 50%;
            border: 4px solid transparent; border-left-color: #a5b4fc; /* indigo-200 */
            animation: spinReverse .75s linear infinite;
        }

        #loadingSpinnerContainer .loading-text{ color:#e5e7eb; font-weight:600; letter-spacing:.02em; }
        #loadingSpinnerContainer .loading-ellipsis span{ display:inline-block; animation: blink 1.4s infinite both; }
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2){ animation-delay:.2s; }
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3){ animation-delay:.4s; }

        @keyframes spin{ to{ transform: rotate(360deg); } }
        @keyframes spinReverse{ to{ transform: rotate(-360deg); } }
        @keyframes blink{
            0%{ opacity:.3; transform: translateY(0); }
            20%{ opacity:1; transform: translateY(-2px); }
            100%{ opacity:.3; transform: translateY(0); }
        }
    </style>

    <div class="max-w-9xl mx-auto w-full py-6">       
        <div class="max-w-9xl mx-auto w-full px-4">
            <div class="gap-6">
                <div class="flex flex-col gap-10">
                    {{-- Form Import --}}            
                    <form id="bqForm" action="{{ $bq ? route('bqsppt.import.edit', $bq->id) : route('bqs.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="idx" value="{{ $bq->id ?? '' }}">
                        <input type="hidden" name="sppjtid" value="{{ $bq->sppjtid ?? '' }}">
                        <input type="hidden" name="bqid" value="{{ $bq->bqid ?? '' }}">
                        <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 shadow border">
                            <div class="flex justify-between border-b pb-2 dark:border-gray-600 mb-4">
                                <h2 class="text-xl font-bold">📥 Import BQ {{ $bq->bq_id }}</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex items-center gap-4">
                                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">BQID</label>
                                    <input class="w-full rounded-md border bg-gray-50 p-2.5 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" value="{{ $bq->bqid }}" readonly>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">SPPT ID</label>
                                    <input class="w-full rounded-md border bg-gray-50 p-2.5 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" value="{{ $bq->sppttid }}" readonly>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Company</label>
                                    <input class="w-full rounded-md border bg-gray-50 p-2.5 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" value="{{ $bq->cpny_id }}" readonly>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">Created By</label>
                                    <input class="w-full rounded-md border bg-gray-50 p-2.5 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" value="{{ $bq->created_by }}" readonly>
                                </div>
                                <div class="flex items-center gap-4 col-span-2">
                                    <label class="block w-40 font-medium text-gray-700 dark:text-gray-300">Import Excel</label>
                                    <input type="file" name="file" id="file" required class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                </div>                                 
                                <div class="col-span-2 flex justify-end">
                                    <button type="submit" id="importBtn" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                                        Import
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Table Preview Import --}}
                    {{-- @if(isset($tempData) && count($tempData) > 0) --}}
                        @php
                            $rows = (isset($tempData) && count($tempData) > 0) ? $tempData : $bq_detail;
                        @endphp
                        <div class="rounded-2xl bg-white p-4 dark:bg-gray-800 shadow border">
                            <h2 class="text-lg font-bold mb-4">
                                📊 BQ Detail
                                @if(isset($tempData) && count($tempData) > 0)
                                    <span class="text-lg font-normal text-red-600">(preview import)</span>
                                @endif
                            </h2>

                            {{-- ✅ Scroll Container --}}
                            <div class="overflow-x-auto w-full">
                                <table class="table-auto min-w-[1500px] w-full border text-sm text-left whitespace-nowrap">
                                    <thead class="bg-gray-100 text-gray-700 font-bold">
                                        <tr>
                                            <th class="px-4 py-2">No</th>
                                            <th class="px-4 py-2">Line No</th>
                                            <th class="px-4 py-2">Description</th>
                                            <th class="px-4 py-2 text-right">Qty</th>
                                            <th class="px-4 py-2">UoM</th>
                                            <th class="px-4 py-2 text-right">Est Mat Price</th>
                                            <th class="px-4 py-2 text-right">Total Est Mat</th>
                                            <th class="px-4 py-2 text-right">Est Jasa Price</th>
                                            <th class="px-4 py-2 text-right">Total Est Jasa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                         @forelse ($rows as $item)
                                            <tr class="border-t bg-gray-50 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                                                <td class="px-4 py-2">{{ $item->bq_no }}</td>
                                                <td class="px-4 py-2">{{ $item->bq_line_no }}</td>
                                                <td class="px-4 py-2">{{ $item->bq_descr }}</td>
                                                <td class="px-4 py-2 text-right">{{ is_null($item->qty) ? '' : number_format((float)$item->qty, 2) }}</td>
                                                <td class="px-4 py-2">{{ $item->uom }}</td>
                                                <td class="px-4 py-2 text-right">{{ is_null($item->est_material_price) ? '' : number_format((float)$item->est_material_price, 2) }}</td>
                                                <td class="px-4 py-2 text-right">{{ is_null($item->total_est_material_price) ? '' : number_format((float)$item->total_est_material_price, 2) }}</td>
                                                <td class="px-4 py-2 text-right">{{ is_null($item->est_jasa_price) ? '' : number_format((float)$item->est_jasa_price, 2) }}</td>
                                                <td class="px-4 py-2 text-right">{{ is_null($item->total_est_jasa_price) ? '' : number_format((float)$item->total_est_jasa_price, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td class="px-4 py-6 text-center text-gray-500 dark:text-gray-300" colspan="9">No detail.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <form id="submitApprovalForm" method="POST" enctype="multipart/form-data">                            
                                @csrf

                                <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                                    <div class="flex w-full flex-col gap-2 rounded-2xl pl-8 pr-8 pt-4">
                                        <div class="flex w-full flex-col">                                           
                                            <details class="group mb-4" open>
                                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                                    <span class="text-lg font-semibold">📸 Photo Before</span>
                                                    <span class="transition-all group-open:hidden">See details</span>
                                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                                </summary>

                                                {{-- ===== EXISTING ATTACHMENTS (thumbnail, clickable, deletable) ===== --}}
                                                <div class="mb-4">
                                                    <div id="existingAttachments"
                                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                                                    @forelse ($attachment as $at)
                                                        @php
                                                        $year    = $at->created_at->year ?? now()->year;
                                                        $fileUrl = url('/attachments/'.$year.'/'.$at->attachfile);
                                                        $ext     = strtolower(pathinfo($at->attachfile, PATHINFO_EXTENSION));
                                                        $isImg   = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                        @endphp

                                                        <div class="relative group rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                                        <a href="{{ $fileUrl }}" target="_blank" class="block aspect-[4/3]">
                                                            @if ($isImg)
                                                            <img src="{{ $fileUrl }}" alt="{{ $at->name }}"
                                                                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                                loading="lazy" referrerpolicy="no-referrer">
                                                            @else
                                                            <div class="flex h-full w-full items-center justify-center bg-gray-50 dark:bg-gray-700">
                                                                <span class="text-4xl">📄</span>
                                                            </div>
                                                            @endif
                                                        </a>

                                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition"></div>

                                                        {{-- filename bar --}}
                                                        <div class="absolute inset-x-0 bottom-0 bg-black/40 px-2 py-1">
                                                            <div class="truncate text-xs text-white" title="{{ $at->name }}">{{ $at->name }}</div>
                                                        </div>

                                                        {{-- delete existing --}}
                                                        <button type="button"
                                                                class="absolute top-2 right-2 bg-white/90 hover:bg-white rounded-full p-1 shadow
                                                                        removeAttachmentExisting"
                                                                data-id="{{ $at->id }}">
                                                            ✕
                                                        </button>
                                                        </div>
                                                    @empty
                                                        <p class="col-span-full text-center italic text-gray-500 dark:text-gray-400">
                                                        No attachments found.
                                                        </p>
                                                    @endforelse
                                                    </div>
                                                </div>

                                                {{-- ===== NEW ATTACHMENTS (grid + hidden inputs in this form) ===== --}}
                                                <div class="flex h-auto flex-col justify-start">
                                                    <div id="hiddenInputs"></div>
                                                    <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                                                    <div id="newAttachmentsGrid"
                                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                                                    {{-- Add Photo tile --}}
                                                    <button type="button" id="addAttachmentTile"
                                                            class="aspect-[4/3] rounded-xl border-2 border-dashed border-gray-300 text-gray-500
                                                                    hover:border-blue-500 hover:text-blue-600 flex items-center justify-center">
                                                        <div class="flex flex-col items-center gap-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span class="text-sm font-medium">Add Photo</span>
                                                        </div>
                                                    </button>
                                                    </div>

                                                    <p class="mt-2 text-xs text-gray-500">Accepted: JPG/PNG, max 5 MB per photo.</p>
                                                </div>
                                                </details>

                                        </div>
                                        <div class="border-b"></div>
                                    </div>
                                    <div class="flex h-auto w-full flex-row justify-end gap-4 pl-4 pr-4">
                                        <div class="w-1/8 flex flex-col justify-start">
                                            <button id="cancelBtn"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
                                                <span id="cancelText">Cancel</span>
                                                <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <input type="hidden" name="temp_id" value="{{ $temp_id }}">
                                        <div class="w-1/8 flex flex-col justify-start">
                                            <button type="submit" id="submitBtn"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                                <span id="btnText">Save</span>
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
        function showOverlay(text='Processing'){
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
            (text || 'Processing') +
            '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true,true).fadeIn(120);
        }
        function hideOverlay(){
            $('#loadingSpinnerContainer').stop(true,true).fadeOut(120);
        }
    </script>

    <script>
        $(function(){
            $('#bqForm').on('submit', function(){
            $('#importBtn').prop('disabled', true).text('Uploading…');
            showOverlay('Uploading');
            });
        });
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function () {
            @if (session('success'))
                toastr.success("{{ session('success') }}", "✅ Success");
            @endif
            @if (session('error'))
                toastr.error("{{ session('error') }}", "❌ Failed");
            @endif
        });
    </script>

    <script>
        $(document).ready(function () {      
            // 🔄 Saat cpny_id berubah
            $('select[name="cpny_id"]').on('change', function () {
                var cpnyId = $(this).val();

                if (cpnyId) {
                    $.ajax({
                        url: '/get-business-units/' + cpnyId,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            var businessUnitSelect = $('select[name="business_unit_id"]');
                            businessUnitSelect.empty(); // kosongkan dulu

                            businessUnitSelect.append('<option value="">Pilih Unit</option>');
                            $.each(data, function (key, value) {
                                businessUnitSelect.append('<option value="' + value.business_unit_id + '">' + value.business_unit_name + '</option>');
                            });
                        }
                    });
                } else {
                    $('select[name="business_unit_id"]').empty();
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#submitApprovalForm').submit(function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('_method', 'PUT');          // spoof → PUT

                /* ⬇️  pakai $bq, bukan $bqs */
                const url = "{{ route('bqs.update', $bq->id) }}";

                $('#submitBtn').attr('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                // $('#loadingSpinner').removeClass('hidden');
                showOverlay('Submitting');

                $.ajax({
                    url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#submitApprovalForm')[0].reset();
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                        toastr.success("Budget Submit Successfully!");
                        window.location.href = "/sppts";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }
                        $('#submitBtn').attr('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        // $('#loadingSpinner').addClass('hidden');
                        hideOverlay();
                    }
                });
            });


            $('#cancelBtn').click(function() {
                const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");

                if (confirmed) {
                    $('#cancelBtn').attr('disabled', true);
                    $('#cancelText').text('Cancelling...');
                    $('#cancelSpinner').removeClass('hidden');

                    // Redirect to /news
                    window.location.href = "{{ route('sppts') }}";
                }
            });
        });
    </script>

    <script>
            $(document).on('click', '.removeAttachment2', function() {
                let attachmentId = $(this).data('id'); // Ambil ID attachment
                let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

                // Cek konfirmasi pengguna
                let confirmDelete = confirm('Are you sure you want to remove this attachment?');

                if (confirmDelete) {
                    $.ajax({
                        url: "/bqs/remove-attachment/" + attachmentId, // Endpoint ke controller
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove(); // Hapus dari tampilan jika berhasil
                                alert("Attachment removed successfully!");
                            } else {
                                alert("Failed to remove attachment.");
                            }
                        },
                        error: function(xhr) {
                            alert("Error! Unable to remove attachment.");
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                    return false;
                }
            });
    </script>
    <script>
(function() {
  // === NEW attachments (client-side preview + hidden file inputs) ===
  const gridNew      = document.getElementById('newAttachmentsGrid');
  const addTile      = document.getElementById('addAttachmentTile');
  const picker       = document.getElementById('hiddenPicker');
  const hiddenInputs = document.getElementById('hiddenInputs');

  const MAX_SIZE  = 5 * 1024 * 1024; // 5MB
  const MAX_FILES = 24;
  const chosenKeys = new Set();

  addTile?.addEventListener('click', () => picker.click());

  picker?.addEventListener('change', function() {
    const files = Array.from(this.files || []);
    files.forEach(file => tryAddFile(file));
    this.value = '';
  });

  function tryAddFile(file) {
    if (!file || !file.type.startsWith('image/')) { toastr?.error?.('File bukan gambar.'); return; }
    if (file.size > MAX_SIZE) { toastr?.error?.(`Ukuran melebihi 5MB: ${file.name}`); return; }
    if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
      toastr?.error?.(`Maksimal ${MAX_FILES} foto.`); return;
    }
    const key = `${file.name}::${file.size}`;
    if (chosenKeys.has(key)) { toastr?.warning?.(`Lewati duplikat: ${file.name}`); return; }
    chosenKeys.add(key);
    addPhotoCard(file, key);
  }

  function addPhotoCard(file, key) {
    // hidden input (agar ikut submit form)
    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'attachments[]';
    input.accept = 'image/*';
    input.className = 'hidden';

    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;

    const id = 'att_' + Math.random().toString(36).slice(2);
    input.dataset.ref = id;
    hiddenInputs.appendChild(input);

    // preview card
    const url = URL.createObjectURL(file);
    const card = document.createElement('div');
    card.className = 'relative group rounded-xl border overflow-hidden';
    card.dataset.ref = id;
    card.innerHTML = `
      <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
      <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
      <button type="button" title="Remove"
              class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">✕</button>
    `;

    // remove (new)
    card.querySelector('button').addEventListener('click', () => {
      const ref = card.dataset.ref;
    //   const hidden = hiddenInputs.querySelector(\`input[data-ref="\${ref}"]\`);
      const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);

      hidden && hidden.remove();
      chosenKeys.delete(key);
      URL.revokeObjectURL(url);
      card.remove();
    });

    gridNew.insertBefore(card, addTile);
  }

  // === EXISTING attachments: delete via AJAX ===
  $(document).on('click', '.removeAttachmentExisting', function(){
    const id   = $(this).data('id');
    const $box = $(this).closest('.group');

    if(!confirm('Remove this attachment?')) return;

    $.ajax({
      url: "/bqs/remove-attachment/" + id, // sesuaikan route remove milik bq
      type: "POST",
      data: { _method: "PUT", _token: "{{ csrf_token() }}" }
    }).done(function(resp){
      if (resp?.success) {
        $box.remove();
        toastr.success('Attachment removed.');
      } else {
        toastr.error(resp?.message || 'Failed to remove attachment.');
      }
    }).fail(function(xhr){
      toastr.error('Error removing attachment.');
      console.error(xhr.responseText);
    });
  });
})();
</script>


</x-app-layout>
