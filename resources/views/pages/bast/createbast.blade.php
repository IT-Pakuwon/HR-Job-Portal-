<x-app-layout>
    {{-- ===== Basic error styles ===== --}}
    <style>
        .is-invalid { border-color: #ef4444 !important; }
        .error-feedback { display:block; color:#dc2626; font-size:12px; margin-top:6px; }
    </style>

    {{-- ===== Overlay styles ===== --}}
    <style>
        #loadingSpinnerContainer{position:fixed;inset:0;display:none;background:rgba(17,24,39,.55);backdrop-filter:blur(2px);z-index:2000}
        #loadingSpinnerContainer .loading-card{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:10px;padding:18px 22px;border-radius:16px;background:linear-gradient(180deg,rgba(31,41,55,.9),rgba(17,24,39,.9));border:1px solid rgba(255,255,255,.08);box-shadow:0 10px 30px rgba(0,0,0,.35), inset 0 0 0 1px rgba(255,255,255,.04)}
        #loadingSpinnerContainer .loading-spinner{width:54px;height:54px;border-radius:50%;border:4px solid transparent;border-top-color:#6366f1;animation:spin 1s linear infinite;position:relative}
        #loadingSpinnerContainer .loading-spinner::after{content:"";position:absolute;inset:6px;border-radius:50%;border:4px solid transparent;border-left-color:#a5b4fc;animation:spinReverse .75s linear infinite}
        #loadingSpinnerContainer .loading-text{color:#e5e7eb;font-weight:600;letter-spacing:.02em}
        #loadingSpinnerContainer .loading-ellipsis span{display:inline-block;animation:blink 1.4s infinite both}
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(2){animation-delay:.2s}
        #loadingSpinnerContainer .loading-ellipsis span:nth-child(3){animation-delay:.4s}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes spinReverse{to{transform:rotate(-360deg)}}
        @keyframes blink{0%{opacity:.3;transform:translateY(0)}20%{opacity:1;transform:translateY(-2px)}100%{opacity:.3;transform:translateY(0)}}
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="bastForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    {{-- penting: hash id term untuk store --}}
                    <input type="hidden" name="term_eid" value="{{ $term_eid }}">
                    <input type="hidden" name="ponbr" value="{{ $term->ponbr }}">

                    {{-- ===== Header ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Create Bast</h2>
                        </div>

                        {{-- Row 1 (5 kolom) --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">PO Nbr</label>
                                <input type="text" value="{{ $term->ponbr }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Company</label>
                                <input type="text" value="{{ $term->cpny_id }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">CS ID</label>
                                <input type="text" value="{{ $term->csid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">SPPB/J/K/T</label>
                                <input type="text" value="{{ $term->sppbjktid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Department</label>
                                <input type="text" value="{{ $term->department_id }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                        </div>

                        {{-- Row 2 (5 kolom) --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">User Peminta</label>
                                <input type="text" value="{{ $term->user_peminta }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Keperluan</label>
                                <input type="text" value="{{ $term->keperluan }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Vendor ID</label>
                                <input type="text" value="{{ $term->vendorid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Vendor</label>
                                <input type="text" value="{{ $term->vendorname }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                        </div>

                        {{-- Row 3 (5 kolom) --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Terms ID</label>
                                <input type="text" value="{{ $term->terms_id }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">TOP ID</label>
                                <input type="text" value="{{ $term->topid }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">TOP Type</label>
                                <input type="text" value="{{ $term->top_type }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Terms Name</label>
                                <input type="text" value="{{ $term->terms_name }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                        </div>

                        {{-- Row 4 (5 kolom) --}}
                        <div class="mt-4 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Progress %</label>
                                <input type="text" value="{{ $term->progress_pct }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Payment %</label>
                                <input type="text" value="{{ $term->payment_pct }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300">Terms Type</label>
                                <input type="text" value="{{ $term->terms_type }}" readonly
                                       class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"/>
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                <div class="flex gap-2">
                                <input type="text" id="lokasi_display"
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Location & Sub Location" readonly>
                                <button type="button" id="btnLokasi"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button>
                                </div>
                                <!-- hidden fields to submit -->
                                <input type="hidden" name="location_id" id="location_id">
                                <input type="hidden" name="sub_location_id" id="sub_location_id">
                            </div>
                            
                            <div></div>
                        </div>
                    </div>

                    {{-- ===== Photo Before ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Photo Before</h2>
                        </div>

                        <div id="bqAttachmentGrid"
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                            {{-- akan dirender via JS --}}
                            <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                                Loading...
                            </p>
                        </div>
                    </div>

                    {{-- ===== Photo After ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-1/2 w-full flex-col border-b p-4">
                            <details class="group mb-4" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-lg font-semibold">Photo After</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <!-- file inputs tersembunyi untuk submit -->
                                    <div id="hiddenInputs"></div>
                                    <!-- picker hidden untuk open file dialog (multiple) -->
                                    <input type="file" id="hiddenPicker" class="hidden" accept="image/*" multiple>

                                    <!-- grid thumbnail -->
                                    <div id="attachmentsGrid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    <!-- tile add photo -->
                                    <button type="button" id="addAttachmentTile"
                                        class="aspect-[4/3] rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500
                                            text-gray-500 hover:text-blue-600 flex items-center justify-center">
                                        <div class="flex flex-col items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="text-sm font-medium">Add Photo</span>
                                        </div>
                                    </button>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Accepted: JPG/PNG, maks 5 MB per foto.</p>
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- Modal Lokasi -->
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="w-[95vw] max-w-2xl rounded-2xl bg-white p-6 dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                            <button type="button" id="closeLokasi" class="text-2xl leading-none text-gray-400 hover:text-gray-600">×</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Kiri: Location -->
                            <div>
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                <select id="modal_location_id"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option value="">-- choose --</option>
                                </select>
                            </div>

                            <!-- Kanan: Sub Location (dependent) -->
                            <div>
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub Location</label>
                                <select id="modal_sub_location_id"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <option value="">-- choose --</option>
                                </select>
                            </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                            <button type="button" id="cancelLokasi" class="rounded-lg border px-4 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                            <button type="button" id="saveLokasi" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Save</button>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Attachments ===== --}}
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments_ba[]"
                                               class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                                class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                               class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Cancel</a>
                            <button type="submit" id="submitBtn"
                                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <span id="btnText">Submit Approval</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===== Overlay HTML ===== --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing
                <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- ===== Overlay helpers ===== --}}
    <script>
        function showOverlay(text='Processing'){
            const $ov=$('#loadingSpinnerContainer');
            $ov.find('.loading-text').html((text||'Processing')+'<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>');
            $ov.stop(true,true).fadeIn(120);
        }
        function hideOverlay(){ $('#loadingSpinnerContainer').stop(true,true).fadeOut(120); }
    </script>

    {{-- ===== Submit (tanpa validasi qty) ===== --}}
    <script>
        $(function(){
            function clearFormErrors(){
                $('#bastForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#bastForm .error-feedback').remove();
            }
            function addError($el,msg){
                if(!$el||!$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid','true');
                if($el.next('.error-feedback').length===0){
                    $el.after('<small class="error-feedback">'+msg+'</small>');
                }
            }
            $(document).on('input change','#bastForm input, #bastForm select',function(){
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('#addAttachment').on('click', function(){
                $('#attachmentsContainer').append(
                    '<div class="attachment-row flex items-center gap-2">'+
                    '<input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">'+
                    '<button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>'+
                    '</div>'
                );
            });
            $(document).on('click','.removeAttachment',function(){
                $(this).closest('.attachment-row').remove();
            });

            $('#bastForm').on('submit', function(e){
                e.preventDefault();
                clearFormErrors();
                $('#submitBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('bastForm'));
                $.ajax({
                    url: "{{ route('bast.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false
                })
                .done(function(res){
                    if (window.toastr) toastr.success(res.message || 'Bast created successfully!');
                    window.location.href = "/bastlist";
                })
                .fail(function(xhr){
                    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                        let msg = 'Mohon periksa input:<br>';
                        Object.keys(xhr.responseJSON.errors).forEach(k=>{
                            msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                        });
                        if (window.toastr) toastr.error(msg);
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        if (window.toastr) toastr.error(xhr.responseJSON.message);
                    } else {
                        if (window.toastr) toastr.error('Error! Please check the input.');
                    }
                })
                .always(function(){
                    $('#submitBtn').prop('disabled', false);
                    $('#btnText').text('Submit Approval');
                    hideOverlay();
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script>
        $(function () {
            // ganti sesuai kebutuhanmu; sesuai snippet awalmu menggunakan $bq->bqid
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $term->bqid]));

            const $grid = $('#bqAttachmentGrid');

            function cardTpl(at) {
                const name    = at.name || at.display_name || '(no name)';
                const by      = at.created_user ?? at.created_by ?? '-';
                const dateStr = at.created_at ? dayjs(at.created_at).format("DD MMM 'YY") : '-';
                const ext     = (at.extention || '').toLowerCase();
                const href    = at.url || '#';
                const isImg   = ['jpg','jpeg','png','gif','webp','bmp','svg','avif'].includes(ext);

                const thumb = isImg && at.url
                    ? `<img src="${href}" alt="${name}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" referrerpolicy="no-referrer">`
                    : `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <span class="text-2xl">${ ext === 'pdf' ? '📕' : '📄' }</span>
                    </div>`;

                return `
                    <div class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800 min-w-[120px]">
                        <a ${at.url ? `href="${href}" target="_blank"` : ''} class="relative block aspect-square overflow-hidden">
                            ${thumb}
                            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                        </a>
                        <div class="px-2 py-2">
                            <div class="truncate text-xs font-medium text-gray-900 dark:text-gray-100" title="${name}">
                                ${name}${ext ? `<span class="text-gray-400">.${ext}</span>` : ''}
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                <div class="truncate text-[11px] text-gray-500 dark:text-gray-400" title="${by}">${by}</div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400 whitespace-nowrap">${dateStr}</div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderGrid(rows) {
                $grid.empty();
                if (!rows || !rows.length) {
                    $grid.append(`
                        <p class="col-span-full py-6 text-center italic text-gray-500 dark:text-gray-400">
                            No attachments found.
                        </p>
                    `);
                    return;
                }
                rows.forEach(at => $grid.append(cardTpl(at)));
            }

            function refresh() {
                $.get(listUrl)
                    .done(res => {
                        if (res.success) renderGrid(res.attachments);
                        else toastr.error(res.message || 'Failed to load attachments.');
                    })
                    .fail(() => toastr.error('Failed to load attachments.'));
            }

            // initial load
            refresh();
        });
    </script>

    <script>
        (function() {
            const grid = document.getElementById('attachmentsGrid');
            const addTile = document.getElementById('addAttachmentTile');
            const picker = document.getElementById('hiddenPicker');
            const hiddenInputs = document.getElementById('hiddenInputs');

            // optional: batasi ukuran per file (5 MB) dan total foto
            const MAX_SIZE = 5 * 1024 * 1024; // 5MB
            const MAX_FILES = 24;

            // hindari duplikat (name+size)
            const chosenKeys = new Set();

            addTile.addEventListener('click', () => picker.click());

            picker.addEventListener('change', function() {
                const files = Array.from(this.files || []);
                files.forEach(file => tryAddFile(file));
                // reset picker agar bisa pilih file yang sama lagi nanti
                this.value = '';
            });

            function tryAddFile(file) {
                if (!file || !file.type.startsWith('image/')) {
                toastr?.error?.('File bukan gambar.'); return;
                }
                if (file.size > MAX_SIZE) {
                toastr?.error?.(`Ukuran melebihi 5MB: ${file.name}`); return;
                }
                if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
                toastr?.error?.(`Maksimal ${MAX_FILES} foto.`); return;
                }
                const key = `${file.name}::${file.size}`;
                if (chosenKeys.has(key)) {
                toastr?.warning?.(`Lewati duplikat: ${file.name}`); return;
                }
                chosenKeys.add(key);
                addPhotoCard(file, key);
            }

            function addPhotoCard(file, key) {
                // buat input file tersembunyi dengan file tunggal
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

                // buat kartu preview
                const url = URL.createObjectURL(file);
                const card = document.createElement('div');
                card.className = 'relative group rounded-xl border overflow-hidden';
                card.dataset.ref = id;
                card.innerHTML = `
                <img src="${url}" alt="attachment" class="w-full h-40 object-cover" />
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition"></div>
                <button type="button" title="Remove"
                    class="absolute top-2 right-2 bg-white/90 rounded-full p-1 shadow hover:bg-white">
                    ✕
                </button>
                `;

                // handler remove
                card.querySelector('button').addEventListener('click', () => {
                // hapus input dan kartu
                const ref = card.dataset.ref;
                const hidden = hiddenInputs.querySelector(`input[data-ref="${ref}"]`);
                hidden && hidden.remove();
                // bersihkan key (duplikat guard) & URL
                chosenKeys.delete(key);
                URL.revokeObjectURL(url);
                card.remove();
                });

                // masukkan kartu sebelum tile "Add Photo"
                grid.insertBefore(card, addTile);
            }
            })();
        </script>

        <script>
            $(function () {

                // cpny langsung dari Blade, tidak pakai select lagi
                const cpny = "{{ $term->cpny_id }}";

                function openLokasiModal(){ 
                    $('#modalLokasi').removeClass('hidden').addClass('flex'); 
                }

                function closeLokasiModal(){ 
                    $('#modalLokasi').addClass('hidden').removeClass('flex'); 
                }

                // buka modal & load locations berdasarkan company
                $('#btnLokasi').on('click', function(){
                    // reset
                    $('#modal_location_id')
                        .empty()
                        .append('<option value="">-- choose --</option>');

                    $('#modal_sub_location_id')
                        .empty()
                        .append('<option value="">-- choose --</option>');

                    // load locations tanpa cek cpny karena sudah fix dari Blade
                    $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`, function(list){
                        list.forEach(it => 
                            $('#modal_location_id').append(
                                new Option(it.text, it.value)
                            )
                        );
                        openLokasiModal();
                    });
                });

                $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

                // ketika pilih location -> load sub locations
                $('#modal_location_id').on('change', function(){
                    const loc = $(this).val();
                    const $sub = $('#modal_sub_location_id');

                    $sub.empty().append('<option value="">-- choose --</option>');

                    if (!loc) return;

                    $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`, function(list){
                        list.forEach(it => 
                            $sub.append(new Option(it.text, it.value))
                        );
                    });
                });

                // simpan pilihan -> tulis ke hidden & display
                $('#saveLokasi').on('click', function(){
                    const locVal = $('#modal_location_id').val();
                    const locTxt = $('#modal_location_id option:selected').text();
                    const subVal = $('#modal_sub_location_id').val();
                    const subTxt = $('#modal_sub_location_id option:selected').text();

                    if (!locVal || !subVal) {
                        toastr.error('Pilih Location dan Sub Location.');
                        return;
                    }

                    $('#location_id').val(locVal);
                    $('#sub_location_id').val(subVal);
                    $('#lokasi_display').val(`${locTxt} — ${subTxt}`);
                    closeLokasiModal();
                });

            });
            </script>



    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
