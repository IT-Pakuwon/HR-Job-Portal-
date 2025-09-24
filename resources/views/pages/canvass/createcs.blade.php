<x-app-layout>
    <style>
        .is-invalid { border-color:#ef4444 !important; }
        .error-feedback { display:block; color:#dc2626; font-size:12px; margin-top:6px; }
    </style>
    <style>
        .req::after { content:" *"; color:#dc2626; font-weight:700; }
    </style>
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
    <style>
        .vendor-title{
            white-space: normal;          /* boleh turun baris */
            overflow-wrap: anywhere;      /* pecah kata sangat panjang */
            word-break: break-word;       /* jaga pecah kata yang wajar */
            line-height: 1.1;             /* rapatkan sedikit */
        }
    </style>



    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="csForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="doc" value="{{ $doc }}">
                    <input type="hidden" name="src_id" value="{{ $src_id }}">
                    <input type="hidden" name="sppbjktid" value="{{ $docno }}">
                    <input type="hidden" name="cpny_id" value="{{ $header->cpny_id }}">
                    <input type="hidden" name="department_id" value="{{ $header->department_id }}">
                    <input type="hidden" name="bqid" value="{{ $header->bqid ?? '' }}">
                    <input type="hidden" name="user_peminta" value="{{ optional($header->creator)->name }}">

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Create CS</h2>                            
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SPPB/J/K/T ID : {{ $docno }}</label>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User : {{ ucwords(strtolower(optional($header->creator)->name)) }}</label>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company : {{ $header->cpny_id }}</label>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department : {{ $header->department_id }}</label>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchaser : {{ ucwords(strtolower(optional($header->purchaser)->name)) }}</label>
                                @if(in_array($doc, ['SPPJ', 'SPPT']))
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        BQ ID : {{ $header->bqid }}
                                    </label>
                                @endif
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Select Vendor</label>
                                <select id="vendorSelect" class="w-64"></select>
                            </div> 
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keperluan : {{ $header->keperluan }}</label>                                
                            </div> 
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note CS :</label>  
                                <textarea name="keperluan" id="keperluan"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    rows="3" ></textarea>                              
                            </div>
                        </div>
                        
                    </div>    
                    
                    <!-- ... header & form atas tetap ... -->
                    <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                    <div class="flex w-full flex-col rounded-2xl p-4">
                        <details class="group" open>                     
                        <summary
                            class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                            <span>CS Detail</span>
                            <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                details &rarr;</span>
                            <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                details &darr;</span>
                        </summary>
                        <div class="flex h-auto flex-col justify-start">
                            <div class="overflow-x-auto">
                                <table id="cvTable" class="w-max table-auto whitespace-nowrap border">
                                    <thead>
                                        <tr class="bg-gray-100 align-top">
                                            <th class="w-64 border px-3 py-2">Inventory Descr</th>
                                            <th class="w-16 border px-3 py-2 text-center">Qty</th>
                                            <th class="w-16 border px-3 py-2 text-center">UOM</th>
                                            <th class="w-16 border px-3 py-2 text-center">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cvBody">
                                        @foreach ($items as $row)
                                            <tr
                                                data-inventoryid="{{ $row->inventoryid ?? '' }}"
                                                data-inventory_descr="{{ $row->inventory_descr }}"
                                                data-uom="{{ $row->uom }}"
                                                data-lastprice="{{ $row->inventory_last_price ?? 0 }}"
                                                data-note="{{ $row->note ?? '' }}"
                                            >
                                                <td class="border px-3 py-2">{{ $row->inventory_descr }}</td>
                                                <td class="border px-3 py-2 text-center">
                                                    <input
                                                        type="text"
                                                        class="qty-input w-24 border rounded px-2 text-right"
                                                        value="{{ number_format((float)$row->qty, 2, ',', '') }}"
                                                        inputmode="decimal"
                                                        autocomplete="off"
                                                        placeholder="0,00"
                                                        aria-label="Qty"
                                                    >
                                                </td>
                                                <td class="border px-3 py-2 text-center">{{ $row->uom }}</td>
                                                <td class="border px-3 py-2 text-center">{{ $row->note }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>


                                    <tfoot>
                                        <tr id="summaryRow" class="bg-gray-50 align-top">
                                            <td colspan="4" class="border px-3 py-2 text-right font-semibold">
                                                Ringkasan
                                            </td>
                                            {{-- sel vendor akan disisipkan via JS --}}
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>                          
                        </div>
                        </details>
                    </div>
                    </div>

                    {{-- ===== Attachments: 2-column layout ===== --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        {{-- Left: Existing Attachments (from controller) --}}
                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            @if(($attachment ?? collect())->count())
                                <details class="group" open>
                                    <summary
                                        class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                        <span>Attachments {{ $doc }}</span>
                                        <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                        <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                    </summary>

                                    <div class="mt-4 overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="text-gray-600 dark:text-gray-300">
                                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                                    <th class="p-3 text-left font-semibold">Filename</th>
                                                    <th class="p-3 text-left font-semibold">Created By</th>
                                                    <th class="p-3 text-left font-semibold">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($attachment as $at)
                                                    @php
                                                        $year = $at->created_at->year;
                                                        $fileUrl = url('/attachments/' . $year . '/' . $at->attachfile);
                                                    @endphp
                                                    <tr class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                                        <td class="p-3">
                                                            <a href="{{ $fileUrl }}" target="_blank"
                                                            class="flex items-center gap-2 font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                                                📎 {{ $at->name }}
                                                            </a>
                                                        </td>
                                                        <td class="p-3">{{ $at->created_user }}</td>
                                                        <td class="p-3">{{ \Carbon\Carbon::parse($at->created_at)->format('d M Y') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </details>
                            @else
                                <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-700">
                                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100">Attachments {{ $doc }}</h3>
                                </div>
                                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Attachment Empty.</p>
                            @endif
                        </div>

                        {{-- Right: New Attachments CS --}}
                        <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                            <details class="group" open>
                                <summary
                                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span>Attachments CS</span>
                                    <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                    <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                                </summary>

                                <div class="flex flex-col pt-6">
                                    <div id="attachmentsContainer">
                                        <div class="attachment-row flex items-center gap-2">
                                            <input type="file" name="attachments[]"
                                                class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                            <button type="button"
                                                    class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" id="addAttachment"
                                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                            clip-rule="evenodd" />
                                    </svg> Add Attachment
                                </button>
                            </details>

                            {{-- Action buttons keep here or move below both columns as you wish --}}
                            <div class="mt-4 flex w-full justify-end gap-4">
                                <button type="button" id="cancelBtn"
                                        class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <span id="cancelText">Cancel</span>
                                    <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                                <button type="button" id="saveBtn"
                                    class="inline-flex items-center justify-center rounded-lg bg-gray-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                    <span>Save CS</span>
                                    <svg id="saveSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                                <button type="submit" id="submitBtn"
                                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <span id="btnText">Submit Approval</span>
                                    <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    {{-- ===== /Attachments: 2-column layout ===== --}}

                </form>
            </div>

            <!-- ========== TAX PICKER MODAL ========== -->
            <div id="taxModal" class="fixed inset-0 z-[3000] hidden">
            <!-- overlay -->
            <div id="taxModalOverlay" class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            <!-- panel -->
            <div class="absolute left-1/2 top-1/2 w-[90vw] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between border-b px-4 py-3 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Pilih Pajak</h3>
                <button id="taxModalClose" class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                </div>
                <div class="p-4">
                <div class="mb-3 flex items-center gap-2">
                    <input id="taxSearch" type="text" placeholder="Cari taxid/descr..."
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                </div>
                <div class="max-h-[55vh] overflow-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Tax ID</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Rate (%)</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                        <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="taxTableBody" class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <!-- rows by JS -->
                    </tbody>
                    </table>
                </div>
                </div>
            </div>
            </div>
            <!-- ========== /TAX PICKER MODAL ========== -->


            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                CS Created Successfully!
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
        // ===== Attachment =====
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
            </div>
        `);
                toggleDeleteButton();
            });

            // Fungsi Hapus Attachment
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            // Fungsi untuk Menampilkan atau Menyembunyikan Tombol Delete
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) {
                    $('.removeAttachment').removeClass('hidden');
                } else {
                    $('.removeAttachment').addClass('hidden');
                }
            }
        });
    </script>   

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(function () {
            /* ===============================
            1) Ambil master vendor -> <select>
            ================================ */
            $('#vendorSelect').empty().append('<option></option>');

            let vendorMaster = []; // cache
            $.getJSON('/vendorscs', function(data) { // route API Anda
                vendorMaster = data || [];
                vendorMaster.forEach(v =>
                    $('#vendorSelect').append(new Option(v.vendor_name, v.id))
                );
            });

            /* ===============================
            2) Init Select2
            ================================ */
            $('#vendorSelect').select2({
                dropdownParent: $('body'),
                placeholder: 'Select',
                width: '350px'
            });

            /* ===============================
            3) Tombol Add Vendor -> buka Select2
            ================================ */
            $('#btnAddVendor').on('click', function() {
                $('#vendorSelect').val(null).trigger('change');
                $('#vendorSelect').select2('open');
            });

            /* ===============================
            4) Saat vendor dipilih -> tambah kolom
            ================================ */
            let vendorCount = 0;

            $('#vendorSelect').on('select2:select', function(e) {
                const id = Number(e.params.data.id);
                const vendor = vendorMaster.find(v => Number(v.id) === id);
                if (!vendor) return;

                // LIMIT 6: hitung kolom vendor yang sudah ada
                const currentCount = $('#cvTable thead th[id^="th-vendor-"]').length;
                if (currentCount >= 6) {
                    toastr.warning('Maksimal 6 vendor.');
                    $(this).val(null).trigger('change');
                    return;
                }

                // Cegah duplikat
                if ($('#th-vendor-' + id).length) {
                    alert('Vendor sudah ada');
                    $(this).val(null).trigger('change');
                    // return;
                }

                addHeader(id, vendor);
                addPriceCells(id);

                vendorCount++;
                $('#emptyMsg').toggle(vendorCount === 0);
                $(this).val(null).trigger('change'); // reset Select2
            });

            /* ===============================
            5) Tambah header vendor + ringkasan per-vendor
            ================================ */
            function addHeader(id, v) {
                const colWidth = '15rem';   
                const $th = $(`
                    <th id="th-vendor-${id}" class="border relative px-3 py-2"
                        style="width:${colWidth}; max-width:${colWidth};"
                        data-vendor-id="${_.escape(v.id)}"
                        data-vendor-code="${_.escape(v.vendor_id)}"
                        data-vendor-name="${_.escape(v.vendor_name)}"
                        data-vendor-addr="${_.escape(v.vendor_addr1 ?? '')}"
                        data-vendor-phone="${_.escape(v.phone_number ?? '')}"
                        data-vendor-cp="${_.escape(v.contact_person ?? '')}"
                    >
                        <div class="vendor-title font-semibold text-center whitespace-normal break-words [overflow-wrap:anywhere] leading-tight">
                        ${v.vendor_name}
                        </div>
                        <div class="text-xs text-gray-500 leading-4 mt-0.5 whitespace-normal break-words">
                            <div>✉️ ${v.contact_person ?? '-'}</div>
                            <div>☎️ ${v.phone_number ?? '-'}</div>
                            <div>🏠 ${v.vendor_addr1 ?? '-'}</div>
                        </div>
                        <div class="mt-1 flex justify-center">
                            <select name="cara_bayar_${id}" class="cara-bayar border rounded text-xs px-1 py-0.5 focus:ring-indigo-500">
                                <option value="14D">14 Days</option>
                                <option value="30D">30 Days</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        <button type="button" class="btn-del absolute -top-1 -right-1 bg-red-600 text-white rounded-full h-5 w-5 flex items-center justify-center text-xs hover:bg-red-700" data-id="${id}">🗑</button>
                    </th>
                `);
                $('#cvTable thead tr').append($th);

                const $sumTd = $(`
                    <td id="td-sum-${id}" class="border px-3 py-2 text-xs space-y-1" style="width:${colWidth}; max-width:${colWidth};">
                        <div><span class="font-semibold">Total&nbsp;</span><span class="sum-total">0</span></div>

                        <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1">
                            <span>PPN&nbsp;</span>
                            <input type="number" class="sum-ppn w-14 border rounded px-1 text-right" value="11.00" step="0.01" min="0">
                            <span>%</span>
                            <button type="button" class="btn-pick-tax rounded border px-2 py-1 text-xs hover:bg-gray-100"
                                    data-for="ppn" data-vendor="${id}" title="Pilih PPN">🔍</button>
                            <input type="hidden" class="sum-ppn-id" value="">
                        </div>

                        <div class="flex items-center gap-1">
                            <span>PPh&nbsp;</span>
                            <input type="number" class="sum-pph w-14 border rounded px-1 text-right" value="0" step="0.01" min="0">
                            <span>%</span>
                            <button type="button" class="btn-pick-tax rounded border px-2 py-1 text-xs hover:bg-gray-100 ml-1"
                                    data-for="pph" data-vendor="${id}" title="Pilih PPh">🔍</button>
                            <input type="hidden" class="sum-pph-id" value="">
                        </div>
                        </div>

                        <div><span class="font-semibold">Grand Total&nbsp;</span><span class="sum-grand">0</span></div>
                        <div><span class="font-semibold">G.Total Selected&nbsp;</span><span class="sum-selected">0</span></div>
                    </td>
                `);

                $('#summaryRow').append($sumTd);

                // Recalc saat PPN/PPh per-vendor berubah
                $sumTd.find('.sum-ppn, .sum-pph').on('input', function() {
                    recalcSummaryVendor(id);
                });
            }

            /* ===============================
            6) Tambah cell harga untuk tiap baris (vendor baru)
            ================================ */
            function addPriceCells(id) {
                $('#cvBody tr').each(function(rowIdx) {

                    const $input = $(`
                    <input
                        type="text"
                        class="price-input w-full border rounded px-1 text-right"
                        data-row="${rowIdx}" data-vendor="${id}"
                        value="0,00" inputmode="decimal" autocomplete="off" placeholder="0,00">
                    `);

                    const $td = $(`
                    <td class="border px-3 py-2">
                        <div class="flex flex-col items-center gap-0.5 w-full"></div>
                    </td>
                    `);

                    const $total = $(`<small class="total-label text-right text-xs font-bold text-gray-600">0</small>`);
                    const $radio = $(`
                    <div class="flex justify-center mt-0.5">
                        <input type="radio" name="selected_vendor_${rowIdx}" value="${id}"
                            class="pick-vendor h-3 w-3 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    </div>
                    `);

                    $td.find('div').append($input, $total, $radio);
                    $(this).append($td);

                    // hitung ulang begitu harga berubah / diformat
                    $input.on('input', function(){ calcCellTotal($(this)); });
                });
            }


            function calcCellTotal($input) {
                const $tr   = $input.closest('tr');
                const qty   = parseQty($tr.find('.qty-input').val());     // sudah kamu buat sebelumnya
                const price = parsePrice($input.val());
                const total = qty * price;

                $input.next('.total-label').text(total.toLocaleString('id-ID'));
                recalcSummaryVendor(Number($input.data('vendor')));
            }

            // Expose ke global agar bisa dipanggil dari event lain
            window.calcCellTotal = function ($input) {
                const $tr   = $input.closest('tr');
                const qty   = parseQty($tr.find('.qty-input').val());
                const price = parsePrice($input.val());
                const total = qty * price;

                // safer: find the label within the same TD as the price input
                $input.closest('td').find('.total-label').text(total.toLocaleString('id-ID'));

                recalcSummaryVendor(Number($input.data('vendor')));
            };


            /* ===============================
            7) Hapus kolom vendor
            ================================ */
            $(document).on('click', '.btn-del', function() {
                const id = $(this).data('id');
                const $header = $('#th-vendor-' + id);
                const colIdx  = $header.index();

                $header.remove();
                $('#td-sum-' + id).remove();
                $('#cvBody tr').each(function() {
                    $(this).children('td').eq(colIdx).remove();
                });

                vendorCount--;
                $('#emptyMsg').toggle(vendorCount === 0);
            });

            // total selected berubah saat radio dipilih
            $(document).on('change', '.pick-vendor', function() {
                const vid = Number($(this).val());
                recalcSummaryVendor(vid);
            });

            // ===== Helper format =====
            window.formatNum = function(n){ return (+n || 0).toLocaleString('id-ID'); }

            // ===== Ringkasan per-vendor =====
            window.recalcSummaryVendor = function(vendorId){
                let total = 0;
                $(`input.price-input[data-vendor="${vendorId}"]`).each(function(){
                    const price = parsePrice($(this).val());
                    const qty   = parseQty($(this).closest('tr').find('.qty-input').val());
                    total += qty * price;
                });

                const $sumCell = $(`#td-sum-${vendorId}`);
                $sumCell.find('.sum-total').text(formatNum(total));

                const ppn   = Number($sumCell.find('.sum-ppn').val() || 0) / 100;
                const pph   = Number($sumCell.find('.sum-pph').val() || 0) / 100;
                const grand = total * (1 + ppn + pph);
                $sumCell.find('.sum-grand').text(formatNum(grand));

                let selTotal = 0;
                $('#cvBody tr').each(function(){
                    const picked = $(this).find('input.pick-vendor:checked').val();
                    if (Number(picked) === vendorId){
                    const lbl = $(this).find(`input.price-input[data-vendor="${vendorId}"]`).next('.total-label');
                    selTotal += Number((lbl.text() || '0').replace(/[^0-9]/g,''));
                    }
                });
                $sumCell.find('.sum-selected').text(formatNum(selTotal));
            }


        });
    </script>
   
   <script>
        // Izinkan: digit, koma, titik, dan tombol kontrol
        $(document).on('keypress', '.qty-input', function (e) {
            const code = e.which || e.keyCode;
            // kontrol: backspace, tab, enter, delete, panah
            if ([8,9,13,37,38,39,40,46].includes(code)) return;

            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) { e.preventDefault(); return; }

            const v = $(this).val() || '';
            // cegah lebih dari satu pemisah desimal total (koma/titik)
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
            e.preventDefault();
            }
        });

      
       // Sanitasi saat user mengetik (hapus karakter asing)
        $(document).on('input', '.qty-input', function () {
            // sanitasi yang sudah kamu punya...
            let v = $(this).val() || '';
            v = v.replace(/[^0-9.,]/g, '');
            const firstSepIdx = v.search(/[.,]/);
            if (firstSepIdx !== -1) {
                const head = v.slice(0, firstSepIdx + 1);
                const tail = v.slice(firstSepIdx + 1).replace(/[.,]/g, '');
                v = head + tail;
            }
            $(this).val(v);

            // 🔁 hitung ulang semua price di baris ini
            const $row = $(this).closest('tr');
            $row.find('input.price-input').each(function () {
                // calcCellTotal($(this));
                window.calcCellTotal($(this));
            });
        });



        // Pada blur → format ke 2 desimal dengan koma
        $(document).on('blur', '.qty-input', function () {
            const num = parseQty($(this).val());
            $(this).val(formatQty2(num));
            // trigger recalculation baris yang terkait (pakai harga yang sudah ada)
            const $row = $(this).closest('tr');
            // Jika ada input harga di baris ini, recal semua vendor di baris
            $row.find('input.price-input').each(function () {
            // calcCellTotal($(this));
            window.calcCellTotal($(this));
            });
        });
    </script>
    <script>
        // Ubah "1.234,56" / "1234,56" / "1234.56" → 1234.56 (Number)
        function parseQty(val) {
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();

            // Buang semua selain digit dan pemisah . atau ,
            val = val.replace(/[^0-9.,]/g, '');

            // Jika ada kedua pemisah, ambil yang terakhir sebagai desimal
            const lastComma = val.lastIndexOf(',');
            const lastDot   = val.lastIndexOf('.');
            let decimalSep  = (lastComma > lastDot) ? ',' : '.';

            // Hilangkan pemisah ribuan (apa pun sebelum decimalSep)
            if (decimalSep === ',') {
            val = val.replace(/\./g, '');     // titik jadi ribuan → buang
            val = val.replace(',', '.');      // koma desimal → titik
            } else {
            val = val.replace(/,/g, '');      // koma ribuan → buang
            // titik sudah desimal → biarkan
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        // Tampilkan Number → "xx,yy" (2 desimal, koma)
        function formatQty2(val) {
            const n = isNaN(val) ? 0 : Number(val);
            return n.toFixed(2).replace('.', ',');
        }
    </script>

    <script>
        // Parse string "1.234,56" / "1,234.56" → Number 1234.56
        function parsePrice(val){
            if (typeof val !== 'string') val = String(val ?? '');
            val = val.trim();

            // buang karakter non digit/pemisah
            val = val.replace(/[^0-9.,]/g, '');

            // tentukan pemisah desimal dengan posisi terakhir
            const lastComma = val.lastIndexOf(',');
            const lastDot   = val.lastIndexOf('.');
            const decimalSep = (lastComma > lastDot) ? ',' : '.';

            if (decimalSep === ','){
            // titik = ribuan → buang; koma = desimal → ganti titik
            val = val.replace(/\./g,'').replace(',', '.');
            } else {
            // koma = ribuan → buang; titik = desimal → biarkan
            val = val.replace(/,/g,'');
            }

            const n = parseFloat(val);
            return isNaN(n) ? 0 : n;
        }

        // Format Number → "1.234,56" (2 desimal, locale id-ID)
        function formatPrice2(n){
            const num = isNaN(n) ? 0 : Number(n);
            return new Intl.NumberFormat('id-ID', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
            }).format(num);
        }
    </script>

    <script>
        // keypress: batasi karakter
        $(document).on('keypress', '.price-input', function(e){
            const code = e.which || e.keyCode;
            // kontrol
            if ([8,9,13,37,38,39,40,46].includes(code)) return;
            const ch = String.fromCharCode(code);
            if (!/[0-9.,]/.test(ch)) { e.preventDefault(); return; }

            const v = $(this).val() || '';
            if ((ch === ',' || ch === '.') && /[.,]/.test(v)) {
            e.preventDefault(); // hanya boleh satu pemisah
            }
        });

        // input: sanitasi agar hanya 1 pemisah
        $(document).on('input', '.price-input', function(){
            let v = $(this).val() || '';
            v = v.replace(/[^0-9.,]/g,'');
            const firstSep = v.search(/[.,]/);
            if (firstSep !== -1){
            const head = v.slice(0, firstSep+1);
            const tail = v.slice(firstSep+1).replace(/[.,]/g,'');
            v = head + tail;
            }
            $(this).val(v);
        });

        // blur: format 2 desimal + ribuan
        $(document).on('blur', '.price-input', function(){
            const num = parsePrice($(this).val());
            $(this).val(formatPrice2(num));  // contoh: 1234.5 → 1.234,50
            // hitung ulang total sel
            calcCellTotal($(this));
        });
    </script>

    <script>
        $(function(){
        // ======== TAX PICKER =========
        let taxCache = null;               // cache data pajak
        let taxTarget = null;              // jQuery input target (sum-ppn/sum-pph)
        let taxTargetVendorId = null;      // vendor id terkait
        let taxTargetType = null;          // 'ppn' | 'pph'

        function openTaxModal($input, vendorId, type){
            taxTarget = $input;
            taxTargetVendorId = vendorId;
            taxTargetType = type;

            const $modal = $('#taxModal');
            $modal.removeClass('hidden');

            if (!taxCache){
            $.getJSON('{{ route('taxes.index') }}', function(data){
                taxCache = Array.isArray(data) ? data : [];
                renderTaxTable(taxCache);
            });
            } else {
            renderTaxTable(taxCache);
            }

            // fokus ke search
            setTimeout(()=> $('#taxSearch').trigger('focus'), 50);
        }

        function closeTaxModal(){
            $('#taxModal').addClass('hidden');
            taxTarget = null;
            taxTargetVendorId = null;
            taxTargetType = null;
        }

        function renderTaxTable(rows){
            const $tbody = $('#taxTableBody');
            $tbody.empty();

            rows.forEach(r => {
            const tr = $(`
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-3 py-2 text-sm">${r.taxid ?? ''}</td>
                <td class="px-3 py-2 text-sm">${Number(r.taxrate ?? 0).toFixed(2)}</td>
                <td class="px-3 py-2 text-sm">${r.descr ?? ''}</td>
                <td class="px-3 py-2 text-right">
                    <button type="button" class="btn-choose-tax rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700"
                            data-taxid="${r.taxid}" data-taxrate="${r.taxrate}">
                    Choose  
                    </button>
                </td>
                </tr>
            `);
            $tbody.append(tr);
            });
        }

        // Buka modal saat klik kaca pembesar
        $(document).on('click', '.btn-pick-tax', function(){
            const vendorId = String($(this).data('vendor'));
            const type = String($(this).data('for')); // 'ppn' | 'pph'
            const $cell = $(`#td-sum-${vendorId}`);
            const $input = (type === 'ppn') ? $cell.find('.sum-ppn') : $cell.find('.sum-pph');

            openTaxModal($input, vendorId, type);
        });

        // Tutup modal
        $('#taxModalClose, #taxModalOverlay').on('click', closeTaxModal);
        $(document).on('keydown', function(e){ if(e.key === 'Escape') closeTaxModal(); });

        // Cari (filter simpel di client)
        $('#taxSearch').on('input', function(){
            const q = ($(this).val() || '').toLowerCase();
            if (!taxCache) return;
            const filtered = taxCache.filter(r => {
            const s1 = String(r.taxid ?? '').toLowerCase();
            const s2 = String(r.descr ?? '').toLowerCase();
            const s3 = String(r.taxrate ?? '').toLowerCase();
            return s1.includes(q) || s2.includes(q) || s3.includes(q);
            });
            renderTaxTable(filtered);
        });

        // Pilih pajak → isi ke input, set hidden taxid, recalc
        $(document).on('click', '.btn-choose-tax', function(){
            if (!taxTarget) return;

            const taxid = $(this).data('taxid');
            const rate  = Number($(this).data('taxrate') || 0);

            // set nilai persentase
            taxTarget.val(rate.toFixed(2));  // 2 desimal (tanpa pemisah), misal 11.00

            // simpan taxid di hidden input sesuai type
            const $cell = $(`#td-sum-${taxTargetVendorId}`);
            if (taxTargetType === 'ppn'){
            $cell.find('.sum-ppn-id').val(taxid);
            } else {
            $cell.find('.sum-pph-id').val(taxid);
            }

            // trigger recalc untuk vendor terkait
            recalcSummaryVendor(String(taxTargetVendorId));

            closeTaxModal();
        });
        });
    </script>
    <script>
        $('#saveBtn').on('click', function(e){
            e.preventDefault();

            // ==== VALIDASI: minimal 1 vendor kolom ====
            const $vendorCols = $('#cvTable thead th[id^="th-vendor-"]');
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            // ==== VALIDASI: total per-vendor tidak semuanya 0 ====
            let allVendorTotalsZero = true;
            $vendorCols.each(function(){
                const vid = String($(this).data('vendor-id'));
                const total = numFromText($(`#td-sum-${vid} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });
            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

            // Kumpulkan vendor summary (urut sesuai posisi kolom)
            const vendors = [];
            $('#cvTable thead th[id^="th-vendor-"]').each(function(i){
                if (vendors.length >= 6) return; // hard limit 6
                const $th = $(this);
                const vid = String($th.data('vendor-id'));
                const vcode = String($th.data('vendor-code'));

                const $sum = $(`#td-sum-${vid}`);
                const total = numFromText($sum.find('.sum-total').text());
                const ppn   = Number($sum.find('.sum-ppn').val() || 0);
                const pph   = Number($sum.find('.sum-pph').val() || 0);
                const ppnId = $sum.find('.sum-ppn-id').val() || '';
                const pphId = $sum.find('.sum-pph-id').val() || '';
                const tax   = total * (ppn/100) + total * (pph/100);
                const grand = total + tax;
                const selTotal = numFromText($sum.find('.sum-selected').text());
                const selTax   = selTotal * (ppn/100) + selTotal * (pph/100);
                const selGrand = selTotal + selTax;

                vendors.push({
                id: vid,
                vendorid: vcode,
                vendorname: String($th.data('vendor-name') || ''),
                vendoralamat: String($th.data('vendor-addr') || ''),
                vendortelp: String($th.data('vendor-phone') || ''),
                vendorcp: String($th.data('vendor-cp') || ''),
                vendortop: $th.find('select.cara-bayar').val() || '',
                vendornote: '',

                total: round2(total),
                ppn: round2(ppn),
                pph: round2(pph),
                taxcode: [ppnId, pphId].filter(Boolean).join('+'),
                tax: round2(tax),
                grand: round2(grand),

                selected_total: round2(selTotal),
                selected_tax: round2(selTax),
                selected_grand: round2(selGrand),
                });
            });

            // Kumpulkan detail baris
            const details = [];
            $('#cvBody tr').each(function(rowIdx){
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const uom = $tr.data('uom') || '';
                const invId = $tr.data('inventoryid') || '';
                const invDescr = $tr.data('inventory_descr') || '';
                const lastPrice = Number($tr.data('lastprice') || 0);
                const csNote = String($tr.data('note') || '');

                const row = {
                inventoryid: invId,
                inventory_descr: invDescr,
                qty: round2(qty),
                uom: uom,
                inventory_last_price: round2(lastPrice),
                csnote_detail: csNote,
                vendor: []
                };

                const picked = String($tr.find('input.pick-vendor:checked').val() || '');

                $('#cvTable thead th[id^="th-vendor-"]').each(function(i){
                if (i >= 6) return;
                const vendorId = String($(this).data('vendor-id'));
                const vendorIdCode = String($(this).data('vendor-code'));
                const $priceInput = $tr.find(`input.price-input[data-vendor="${vendorId}"]`);
                const price = parsePrice($priceInput.val());
                const total = qty * price;

                row.vendor.push({
                    id: vendorId,
                    vendorid: vendorIdCode,
                    price: round2(price),
                    total: round2(total),
                    selected: vendorId === picked
                });
                });

                details.push(row);
            });

            // FormData dari form yang benar
            const fd = new FormData(document.getElementById('csForm'));
            fd.append('vendors', JSON.stringify(vendors));
            fd.append('details', JSON.stringify(details));

            showOverlay('Submitting');

            $.ajax({
                url: "{{ route('cs.save') }}",
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res){
                hideOverlay();
                toastr.success('CS berhasil disimpan.');
                // window.location.href = res.redirect ?? window.location.href;
                },
                error: function(xhr){
                hideOverlay();
                let msg = 'Gagal menyimpan CS.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                toastr.error(msg);
                }
            });
            });


        // helpers number
        function numFromText(t){
            t = String(t || '');
            t = t.replace(/\./g,'').replace(',', '.').replace(/[^0-9.-]/g,'');
            const n = parseFloat(t);
            return isNaN(n) ? 0 : n;
        }
        function round2(n){ return Math.round((+n + Number.EPSILON) * 100) / 100; }
    </script>

    <script>
        $('#submitBtn').on('click', function(e){
            e.preventDefault();

            // ==== VALIDASI: minimal 1 vendor kolom ====
            const $vendorCols = $('#cvTable thead th[id^="th-vendor-"]');
            if ($vendorCols.length === 0) {
                toastr.error('Pilih minimal 1 vendor.');
                return;
            }

            // ==== VALIDASI: total per-vendor tidak semuanya 0 ====
            let allVendorTotalsZero = true;
            $vendorCols.each(function(){
                const vid = String($(this).data('vendor-id'));
                const total = numFromText($(`#td-sum-${vid} .sum-total`).text());
                if (total > 0) allVendorTotalsZero = false;
            });
            if (allVendorTotalsZero) {
                toastr.error('Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
                return;
            }

             // ==== NEW: minimal ada vendor TERPILIH ====
    if ($('.pick-vendor:checked').length === 0) {
      toastr.error('Pilih vendor pada minimal satu item.');
      return;
    }

    // ==== NEW: baris yang ada harga > 0 harus pilih vendor ====
    let rowWithoutVendor = false;
    $('#cvBody tr').each(function(){
      // ada harga > 0 di salah satu vendor pada baris ini?
      let hasPrice = false;
      $(this).find('input.price-input').each(function(){
        const v = $(this).val() || '';
        const num = (function parsePriceLocal(val){
          let t = String(val).trim().replace(/[^0-9.,]/g,'');
          const lc = t.lastIndexOf(','), ld = t.lastIndexOf('.');
          const dec = (lc > ld) ? ',' : '.';
          if (dec === ',') t = t.replace(/\./g,'').replace(',', '.');
          else t = t.replace(/,/g,'');
          const n = parseFloat(t);
          return isNaN(n) ? 0 : n;
        })(v);
        if (num > 0) { hasPrice = true; return false; }
      });

      // kalau ada harga, wajib ada vendor dipilih di baris ini
      if (hasPrice && $(this).find('.pick-vendor:checked').length === 0) {
        rowWithoutVendor = true;
        return false; // break
      }
    });
    if (rowWithoutVendor) {
      toastr.error('Ada baris yang memiliki harga tetapi belum memilih vendor.');
      return;
    }

            // Kumpulkan vendor summary (urut sesuai posisi kolom)
            const vendors = [];
            $('#cvTable thead th[id^="th-vendor-"]').each(function(i){
                if (vendors.length >= 6) return; // hard limit 6
                const $th = $(this);
                const vid = String($th.data('vendor-id'));
                const vcode = String($th.data('vendor-code'));

                const $sum = $(`#td-sum-${vid}`);
                const total = numFromText($sum.find('.sum-total').text());
                const ppn   = Number($sum.find('.sum-ppn').val() || 0);
                const pph   = Number($sum.find('.sum-pph').val() || 0);
                const ppnId = $sum.find('.sum-ppn-id').val() || '';
                const pphId = $sum.find('.sum-pph-id').val() || '';
                const tax   = total * (ppn/100) + total * (pph/100);
                const grand = total + tax;
                const selTotal = numFromText($sum.find('.sum-selected').text());
                const selTax   = selTotal * (ppn/100) + selTotal * (pph/100);
                const selGrand = selTotal + selTax;

                vendors.push({
                id: vid,
                vendorid: vcode,
                vendorname: String($th.data('vendor-name') || ''),
                vendoralamat: String($th.data('vendor-addr') || ''),
                vendortelp: String($th.data('vendor-phone') || ''),
                vendorcp: String($th.data('vendor-cp') || ''),
                vendortop: $th.find('select.cara-bayar').val() || '',
                vendornote: '',

                total: round2(total),
                ppn: round2(ppn),
                pph: round2(pph),
                taxcode: [ppnId, pphId].filter(Boolean).join('+'),
                tax: round2(tax),
                grand: round2(grand),

                selected_total: round2(selTotal),
                selected_tax: round2(selTax),
                selected_grand: round2(selGrand),
                });
            });

            // Kumpulkan detail baris
            const details = [];
            $('#cvBody tr').each(function(rowIdx){
                const $tr = $(this);
                const qty = parseQty($tr.find('.qty-input').val());
                const uom = $tr.data('uom') || '';
                const invId = $tr.data('inventoryid') || '';
                const invDescr = $tr.data('inventory_descr') || '';
                const lastPrice = Number($tr.data('lastprice') || 0);
                const csNote = String($tr.data('note') || '');

                const row = {
                inventoryid: invId,
                inventory_descr: invDescr,
                qty: round2(qty),
                uom: uom,
                inventory_last_price: round2(lastPrice),
                csnote_detail: csNote,
                vendor: []
                };

                const picked = String($tr.find('input.pick-vendor:checked').val() || '');

                $('#cvTable thead th[id^="th-vendor-"]').each(function(i){
                if (i >= 6) return;
                const vendorId = String($(this).data('vendor-id'));
                const vendorIdCode = String($(this).data('vendor-code'));
                const $priceInput = $tr.find(`input.price-input[data-vendor="${vendorId}"]`);
                const price = parsePrice($priceInput.val());
                const total = qty * price;

                row.vendor.push({
                    id: vendorId,
                    vendorid: vendorIdCode,
                    price: round2(price),
                    total: round2(total),
                    selected: vendorId === picked
                });
                });

                details.push(row);
            });

            // FormData dari form yang benar
            const fd = new FormData(document.getElementById('csForm'));
            fd.append('vendors', JSON.stringify(vendors));
            fd.append('details', JSON.stringify(details));

            showOverlay('Submitting');

            $.ajax({
                url: "{{ route('cs.store') }}",
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                success: function(res){
                hideOverlay();
                toastr.success('CS berhasil disimpan.');
                // window.location.href = res.redirect ?? window.location.href;
                },
                error: function(xhr){
                hideOverlay();
                let msg = 'Gagal menyimpan CS.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                toastr.error(msg);
                }
            });
            });


        // helpers number
        function numFromText(t){
            t = String(t || '');
            t = t.replace(/\./g,'').replace(',', '.').replace(/[^0-9.-]/g,'');
            const n = parseFloat(t);
            return isNaN(n) ? 0 : n;
        }
        function round2(n){ return Math.round((+n + Number.EPSILON) * 100) / 100; }
    </script>


    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/lodash@4/lodash.min.js"></script>    

</x-app-layout>