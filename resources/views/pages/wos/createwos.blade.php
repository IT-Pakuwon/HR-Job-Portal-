<x-app-layout>




    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="woForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between border-b pb-3 dark:border-gray-700">
                            <h2 class="text-base font-bold text-gray-800 dark:text-white">📄 Create WO</h2>
                        </div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <!-- Company -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select name="departementid" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- WO Type -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">WO Type</label>
                                <select name="wotype" id="wotype" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">choose </option>
                                </select>
                            </div>

                            <!-- WO Request -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">WO
                                    Request</label>
                                <select name="worequest" id="worequest" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">choose </option>
                                </select>
                            </div>
                        </div>

                        <!-- Row 2 -->
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                            <!-- Jenis Pekerjaan -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Jenis
                                    Pekerjaan</label>
                                <div class="flex gap-2">
                                    <input id="jenis_pekerjaan_display" readonly
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Worktype & Subworktype">
                                    {{-- <button id="btnJenisPekerjaan" type="button"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button> --}}
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="Lookup">
                                        🔎
                                    </button>
                                </div>
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <!-- Lokasi -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                                <div class="flex gap-2">
                                    <input id="lokasi_display" readonly
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Location & Sub Location">
                                    {{-- <button id="btnLokasi" type="button"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button> --}}
                                    <button type="button" id="btnLokasi"
                                        class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="Lookup">
                                        🔎
                                    </button>
                                </div>
                                <input type="hidden" name="location_id" id="location_id">
                                <input type="hidden" name="sub_location_id" id="sub_location_id">
                            </div>

                        </div>

                        <!-- Row 3 -->
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <!-- Perpost -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @php $year = now()->year; @endphp
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    <option value="{{ $year + 1 }}">{{ $year + 1 }}</option>
                                </select>
                            </div>
                            <!-- PIC Requester -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">PIC
                                    Requester</label>
                                <input type="text" name="picrequester" id="picrequester"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    value="{{ auth()->user()->username }}" required>
                            </div>

                            <!-- Biaya WO -->
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Biaya WO</label>
                                <input type="text" name="biaya_wo" id="biaya_wo" inputmode="decimal"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    placeholder="0,00">
                                <small id="biaya_wo_error" class="text-red-600" style="display:none;"></small>
                            </div>

                            <!-- Budget -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Budget</label>
                                <select name="wobudget" id="wobudget" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">choose </option>
                                    <option value="Internal">Pemberi Kerja</option>
                                    <option value="External">Penerima Kerja</option>
                                </select>
                            </div>


                        </div>

                        <!-- COA -->
                        <div id="coaGroup" class="mt-6">
                            <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">COA</label>
                            <div class="flex gap-2">
                                <input type="text" id="budget_display" readonly
                                    class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    placeholder="Pilih Budget">
                                {{-- <button id="btnBudget" type="button"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button> --}}
                                <button type="button" id="btnBudget"
                                    class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="Lookup">
                                    🔎
                                </button>
                            </div>

                            <!-- hidden -->
                            <input type="hidden" name="activity_id" id="activity_id">
                            <input type="hidden" name="business_unit_id" id="business_unit_id">
                            <input type="hidden" name="department_fin_id" id="department_fin_id">
                            <input type="hidden" name="coa_id" id="coa_id">
                            <input type="hidden" name="activity_descr" id="activity_descr">
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                            <textarea name="keperluan" id="keperluan" rows="3" required
                                class="mt-2 w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"></textarea>
                        </div>
                    </div>


                    <!-- Modal -->
                    <div id="modalJenisPekerjaan"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="w-[95vw] max-w-2xl rounded-xl bg-white p-4 dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Pilih Jenis Pekerjaan
                                </h3>
                                <button type="button" id="closeJenisPekerjaan"
                                    class="text-lg leading-none text-gray-400 hover:text-gray-600">×</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Kiri: Worktype -->
                                <div>
                                    <label
                                        class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">choose </option>
                                    </select>
                                </div>

                                <!-- Kanan: Subworktype (dependent) -->
                                <div>
                                    <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Worktype</label>
                                    <select id="modal_subworktypeid"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">choose </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" id="cancelJenisPekerjaan"
                                    class="rounded-lg border px-4 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                                <button type="button" id="saveJenisPekerjaan"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Lokasi -->
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="w-[95vw] max-w-2xl rounded-xl bg-white p-4 dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                                <button type="button" id="closeLokasi"
                                    class="text-lg leading-none text-gray-400 hover:text-gray-600">×</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Kiri: Location -->
                                <div>
                                    <label
                                        class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                    <select id="modal_location_id"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">choose </option>
                                    </select>
                                </div>

                                <!-- Kanan: Sub Location (dependent) -->
                                <div>
                                    <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Location</label>
                                    <select id="modal_sub_location_id"
                                        class="mt-1 w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">choose </option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" id="cancelLokasi"
                                    class="rounded-lg border px-4 py-2 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">Cancel</button>
                                <button type="button" id="saveLokasi"
                                    class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- ===== Modal Lookup COA ===== -->
                    <div id="coaModal"
                        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-black/40 p-4">
                        <div class="w-full max-w-4xl rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                            <div class="mb-3 flex items-center justify-between border-b pb-2">
                                <h3 class="text-sm font-bold text-gray-800 dark:text-white">Select COA</h3>
                                <button type="button" id="closeCoaModal"
                                    class="rounded px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">✖</button>
                            </div>

                            <div class="mb-3 flex items-center gap-2 text-sm">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="rounded border border-gray-300 bg-white px-3 py-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                <button id="coaRefresh" type="button"
                                    class="rounded border px-3 py-1 hover:bg-gray-100 dark:hover:bg-gray-700">↻</button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Company: <b id="coaCpnyBadge"></b></span>
                                    <span>Dept: <b id="coaDeptBadge"></b></span>
                                    <span>Perpost: <b id="coaPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto">
                                <table class="w-full text-left">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border p-2">Account ID</th>
                                            <th class="border p-2">Activity</th>
                                            <th class="border p-2">Remaining Budget</th>
                                            <th class="w-24 border p-2 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coaTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-sm">
                                <span id="coaCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="coaPrev" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Prev</button>
                                    <button id="coaNext" type="button"
                                        class="rounded border px-3 py-1 disabled:opacity-40">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>




                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg> Add Attachment
                            </button>
                        </details>

                        <div
                            class="mt-4 flex flex-row justify-between gap-4 md:flex-row md:items-center md:justify-between">
                            <button id="backBtn" onclick="history.back()"
                                class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>

                                <span>Back</span>
                            </button>

                            <div class="flex justify-start md:justify-end">
                                <button type="submit" id="submitBtn"
                                    class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    <span id="btnText">Submit Approval</span>
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

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                Wo Created Successfully!
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
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            // pastikan tampil (tetap bisa fadeIn)
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $dept = $('select[name="departementid"]');

            // ====== Load Categories (wotype & worequest) ======
            function loadCategories($select, categoryid) {
                $select.empty().append('<option value="">choose </option>');
                $.getJSON(`/wos/ajax/categories/${encodeURIComponent(categoryid)}?doctype=WO`)
                    .done(function(list) {
                        list.forEach(function(it) {
                            // value & text sama = category_name
                            $select.append(new Option(it.text, it.text));
                        });
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat data kategori.');
                    });
            }

            loadCategories($('#wotype'), 'wotype');
            loadCategories($('#worequest'), 'worequest');


            // ====== Modal Jenis Pekerjaan ======
            function openJenisModal() {
                $('#modalJenisPekerjaan').removeClass('hidden').addClass('flex');
            }

            function closeJenisModal() {
                $('#modalJenisPekerjaan').addClass('hidden').removeClass('flex');
            }

            $('#btnJenisPekerjaan').on('click', function() {
                // load worktypes (optional: kirim departementid sebagai filter)
                const params = $.param({
                    departementid: $dept.val() || ''
                });
                $('#modal_worktypeid').empty().append('<option value="">choose </option>');
                $('#modal_subworktypeid').empty().append('<option value="">choose </option>');
                $.getJSON(`/wos/ajax/worktypes?${params}`, function(list) {
                    list.forEach(it => $('#modal_worktypeid').append(new Option(it.text, it
                        .value)));
                    openJenisModal();
                });
            });

            $('#closeJenisPekerjaan, #cancelJenisPekerjaan').on('click', closeJenisModal);

            // when worktype selected → load subworktypes
            // $('#modal_worktypeid').on('change', function(){
            //     const wt = $(this).val();
            //     const $sub = $('#modal_subworktypeid');
            //     $sub.empty().append('<option value="">choose </option>');
            //     if (!wt) return;
            //     $.getJSON(`/wos/ajax/subworktypes/${encodeURIComponent(wt)}`, function(list){
            //     list.forEach(it => $sub.append(new Option(it.text, it.value)));
            //     });
            // });
            // ketika worktype dipilih → load subworktypes (doctype=WO)
            $('#modal_worktypeid').on('change', function() {
                const wt = $(this).val();
                const $sub = $('#modal_subworktypeid');
                $sub.empty().append('<option value="">choose </option>');
                if (!wt) return;

                const doctype = 'WO'; // kirim dari view
                $.getJSON(
                    `/wos/ajax/subworktypes/${encodeURIComponent(wt)}?doctype=${encodeURIComponent(doctype)}`,
                    function(list) {
                        list.forEach(it => $sub.append(new Option(it.text, it.value)));
                    });
            });


            // Save modal selection → write to hidden fields
            $('#saveJenisPekerjaan').on('click', function() {
                const wtVal = $('#modal_worktypeid').val();
                const wtTxt = $('#modal_worktypeid option:selected').text();
                const swVal = $('#modal_subworktypeid').val();
                const swTxt = $('#modal_subworktypeid option:selected').text();

                if (!wtVal || !swVal) {
                    toastr.error('Pilih Worktype dan Sub Worktype.');
                    return;
                }
                $('#worktypeid').val(wtVal);
                $('#subworktypeid').val(swVal);
                $('#jenis_pekerjaan_display').val(`${wtTxt} — ${swTxt}`);
                closeJenisModal();
            });
        });
    </script>

    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');

            function openLokasiModal() {
                $('#modalLokasi').removeClass('hidden').addClass('flex');
            }

            function closeLokasiModal() {
                $('#modalLokasi').addClass('hidden').removeClass('flex');
            }

            // buka modal & load locations berdasarkan company
            $('#btnLokasi').on('click', function() {
                const cpny = $cpny.val();
                if (!cpny) {
                    toastr.error('Pilih Company terlebih dahulu.');
                    return;
                }
                // reset
                $('#modal_location_id').empty().append('<option value="">choose </option>');
                $('#modal_sub_location_id').empty().append('<option value="">choose </option>');
                // load locations
                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`, function(list) {
                    list.forEach(it => $('#modal_location_id').append(new Option(it.text, it
                        .value)));
                    openLokasiModal();
                });
            });

            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            // ketika pilih location -> load sub locations
            $('#modal_location_id').on('change', function() {
                const cpny = $cpny.val();
                const loc = $(this).val();
                const $sub = $('#modal_sub_location_id');
                $sub.empty().append('<option value="">choose </option>');
                if (!cpny || !loc) return;
                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`,
                    function(list) {
                        list.forEach(it => $sub.append(new Option(it.text, it.value)));
                    });
            });

            // simpan pilihan -> tulis ke hidden & display
            $('#saveLokasi').on('click', function() {
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

            // jika company berubah, kosongkan pilihan lokasi sebelumnya (karena depend on cpny)
            $cpny.on('change', function() {
                $('#location_id, #sub_location_id, #lokasi_display').val('');
            });
        });
    </script>

    <script>
        $(function() {
            function clearAllErrors(scope = '#woForm') {
                $(scope).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $(scope).find('.error-feedback').remove();
            }

            function addError($el, message) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + message + '</small>');
                }
            }

            $('#woForm').on('submit', function(e) {
                e.preventDefault();
                clearAllErrors();

                const $cpny = $('select[name="cpnyid"]');
                const $dept = $('select[name="departementid"]');
                const $wotype = $('#wotype');
                const $worequest = $('#worequest');
                const $wt = $('#worktypeid');
                const $swt = $('#subworktypeid');
                const $loc = $('#location_id');
                const $subloc = $('#sub_location_id');
                const $pic = $('#picrequester');
                const $biaya = $('#biaya_wo');

                let ok = true;
                if (!$cpny.val()) {
                    addError($cpny, 'Company wajib.');
                    ok = false;
                }
                if (!$dept.val()) {
                    addError($dept, 'Department wajib.');
                    ok = false;
                }
                if (!$wotype.val()) {
                    addError($wotype, 'WO Type wajib.');
                    ok = false;
                }
                if (!$worequest.val()) {
                    addError($worequest, 'WO Request wajib.');
                    ok = false;
                }
                if (!$wt.val()) {
                    addError($('#jenis_pekerjaan_display'), 'Pilih Worktype.');
                    ok = false;
                }
                if (!$swt.val()) {
                    addError($('#jenis_pekerjaan_display'), 'Pilih Sub Worktype.');
                    ok = false;
                }
                if (!$loc.val()) {
                    addError($('#lokasi_display'), 'Location wajib.');
                    ok = false;
                }
                if (!$subloc.val()) {
                    addError($('#lokasi_display'), 'Sub Location wajib.');
                    ok = false;
                }
                if (!$pic.val()) {
                    addError($pic, 'PIC Requester wajib.');
                    ok = false;
                }
                if ($biaya.val() && isNaN(parseFloat($biaya.val()))) {
                    addError($biaya, 'Biaya WO tidak valid.');
                    ok = false;
                }

                const $wobudget = $('#wobudget');
                const needsCoa = $wobudget.val() === 'Internal';
                if (needsCoa && !$('#coa_id').val()) {
                    addError($('#budget_display'), 'Silakan pilih COA.');
                    ok = false;
                }

                if (!ok) {
                    toastr.error('Mohon lengkapi input yang wajib.');
                    const $first = $('#woForm .is-invalid').first();
                    if ($first.length) $('html,body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 300);
                    return;
                }



                $('#submitBtn').prop('disabled', true);
                $('#cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const formData = new FormData(document.getElementById('woForm'));
                $.ajax({
                        url: "{{ route('wos.store') }}",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || "WO created successfully!");
                        window.location.href = "/wos";
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            toastr.error(msg);
                        } else if (xhr.responseJSON?.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        hideOverlay();
                    });
            });
        });
    </script>


    <script>
        // ===== Attachment =====
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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

    <script>
        $(function() {
            $('#cancelBtn').on('click', function() {
                if (confirm('Batalkan pembuatan WO ini? Perubahan belum disimpan.')) {
                    // sesuaikan: history back atau route index
                    if (document.referrer) {
                        window.history.back();
                    } else {
                        window.location.href = "/wos";
                    }
                }
            });
        });
    </script>

    <script>
        $(function() {
            const $wobudget = $('#wobudget');
            const $coaGroup = $('#coaGroup');

            function clearCoaFields() {
                $('#coa_id, #activity_id, #business_unit_id, #department_fin_id, #activity_descr').val('');
                $('#budget_display').val('');
            }

            function applyBudgetVisibility() {
                const val = $wobudget.val();
                if (val === 'Internal') { // Pemberi Kerja
                    $coaGroup.slideDown(120);
                } else { // External = Penerima Kerja
                    $coaGroup.slideUp(120);
                    clearCoaFields();
                }
            }

            // init on load
            applyBudgetVisibility();

            // on change
            $wobudget.on('change', applyBudgetVisibility);
        });
    </script>

    <script>
        function formatNumber(num, isCost = false) {
            if (num === null || num === undefined || num === '') return '';

            num = parseFloat(num);

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: isCost ? 2 : 0,
                maximumFractionDigits: isCost ? 2 : 0
            }).format(num);
        }
    </script>

    <script>
        $(function() {
            // ===== COA modal state =====
            const $coaModal = $('#coaModal');
            const $coaTbody = $('#coaTableBody');
            const $coaCount = $('#coaCount');
            const $coaCpny = $('#coaCpnyBadge');
            const $coaDept = $('#coaDeptBadge');
            const $coaPerpost = $('#coaPerpostBadge');

            const $btnBudget = $('#btnBudget');

            let coaState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
            };

            function openCoaModal() {
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = $('#perpost').val();

                if (!cpny) {
                    if (window.toastr) toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!dept) {
                    if (window.toastr) toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }

                coaState.cpnyid = cpny;
                coaState.deptid = dept;
                coaState.perpost = perpost;
                coaState.page = 1;
                coaState.search = '';

                $coaCpny.text(coaState.cpnyid);
                $coaDept.text(coaState.deptid);
                $coaPerpost.text(coaState.perpost || '');
                $('#coaSearch').val('');

                $coaModal.removeClass('hidden').addClass('flex');
                loadCoa();
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            // open via button in COA block
            $btnBudget.on('click', openCoaModal);

            // close modal
            $('#closeCoaModal').on('click', closeCoaModal);
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $coaModal.is(':visible')) closeCoaModal();
            });

            // Search & refresh
            $('#coaSearch').on('input', function() {
                coaState.search = $(this).val().trim();
                coaState.page = 1;
                loadCoa();
            });
            $('#coaRefresh').on('click', function() {
                $('#coaSearch').val('');
                coaState.search = '';
                coaState.page = 1;
                loadCoa();
            });

            // Pagination
            $('#coaPrev').on('click', function() {
                if (coaState.page > 1) {
                    coaState.page--;
                    loadCoa();
                }
            });
            $('#coaNext').on('click', function() {
                const maxPage = Math.ceil(coaState.total / coaState.per_page);
                if (coaState.page < maxPage) {
                    coaState.page++;
                    loadCoa();
                }
            });

            // Load COA from API
            function loadCoa() {
                $coaTbody.html('<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>');
                $.getJSON("{{ route('coa.byDeptWo') }}", {
                        cpnyid: coaState.cpnyid,
                        deptid: coaState.deptid,
                        perpost: coaState.perpost,
                        search: coaState.search,
                        page: coaState.page,
                        per_page: coaState.per_page
                    })
                    .done(function(res) {
                        // Expected: { data: [{account_id, activity_id, business_unit_id, department_fin_id, activity_descr, totalbudget}], total }
                        const rows = (res.data || []).map(item => {
                            const id = item.account_id ?? '';
                            const actId = item.activity_id ?? '';
                            const buId = item.business_unit_id ?? '';
                            const deptFinId = item.department_fin_id ?? '';
                            const actDetail = item.activity_descr ?? '';
                            const available = formatNumber(item.availablebudget) ?? '';
                            const used = formatNumber(item.usedbudget) ?? '';
                            const remaining = formatNumber(item.remaining) ?? '';

                            // label yang tampil di input display
                            const label = id ? `${id}${actDetail ? ' - ' + actDetail : ''}` : (
                                actDetail || '');

                            return `
                    <tr>
                    <td class="border p-2">${id}</td>
                    <td class="border p-2">${actDetail}</td>
                    <td class="border p-2">
                        <div class="font-semibold">${remaining}</div>
                        <div class=" text-sm  opacity-70">Available : ${available}</div>
                        <div class=" text-sm  opacity-70">Used: ${used}</div>
                    </td>
                    <td class="border p-2 text-center">
                        <button type="button" class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100"
                                data-id="${id}"
                                data-activity_id="${actId}"
                                data-business_unit_id="${buId}"
                                data-department_fin_id="${deptFinId}"
                                data-activity_descr="${$('<div>').text(actDetail).html()}"
                                data-label="${$('<div>').text(label).html()}">
                        Choose
                        </button>
                    </td>
                    </tr>
                `;
                        }).join('');

                        $coaTbody.html(rows || '<tr><td colspan="4" class="p-3 text-center">No data</td></tr>');
                        coaState.total = res.total || 0;
                        const shown = rows ? (res.data || []).length : 0;
                        $coaCount.text(`Showing ${shown} of ${coaState.total} items`);

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load</td></tr>'
                        );
                        $coaCount.text('');
                        $('#coaPrev, #coaNext').prop('disabled', true);
                    });
            }

            // Choose -> isi field by ID (single COA di form)
            $(document).on('click', '.chooseCoa', function() {
                const id = $(this).data('id') || '';
                const actId = $(this).data('activity_id') || '';
                const buId = $(this).data('business_unit_id') || '';
                const deptFinId = $(this).data('department_fin_id') || '';
                const actDescr = $(this).data('activity_descr') || '';
                const label = $(this).data('label') || '';

                $('#coa_id').val(id);
                $('#activity_id').val(actId);
                $('#business_unit_id').val(buId);
                $('#department_fin_id').val(deptFinId);
                $('#activity_descr').val(actDescr);
                $('#budget_display').val($('<div>').html(label).text()); // unescape label untuk display

                // bersihkan error jika ada
                $('#budget_display').removeClass('is-invalid').next('.error-feedback').remove();

                closeCoaModal();
            });

            // Jika cpny/dept/perpost berubah saat modal terbuka → refresh
            $('select[name="cpnyid"], select[name="departementid"], #perpost').on('change', function() {
                if ($coaModal.is(':visible')) {
                    coaState.cpnyid = $('select[name="cpnyid"]').val();
                    coaState.deptid = $('select[name="departementid"]').val();
                    coaState.perpost = $('#perpost').val();
                    $coaCpny.text(coaState.cpnyid || '-');
                    $coaDept.text(coaState.deptid || '-');
                    $coaPerpost.text(coaState.perpost || '-');
                    coaState.page = 1;
                    loadCoa();
                }
            });
        });
    </script>

    <script>
        $(function() {
            const $biaya = $("#biaya_wo");
            const $err = $("#biaya_wo_error");

            // 1) Matikan semua listener lama yg mungkin nempel & buang maxlength
            $biaya.off();
            $biaya.removeAttr("maxlength");

            // Helper: format integer -> ribuan id-ID
            function formatIntID(intDigits) {
                if (!intDigits) return "0";
                intDigits = intDigits.replace(/^0+(?!$)/, ""); // trim leading zero berlebih
                const n = parseInt(intDigits, 10);
                return isNaN(n) ? "0" : n.toLocaleString("id-ID");
            }

            // Bersihkan value menjadi: [digits][,digits<=2], TANPA titik ribuan
            function sanitizeLive(val) {
                val = (val || "").replace(/[^0-9,]/g, ""); // hanya angka & koma
                const parts = val.split(",");
                let intPart = parts[0] || "";
                let fracPart = parts[1] || "";

                // buang titik apapun yg mungkin tersisa + non-digit
                intPart = intPart.replace(/\./g, "").replace(/[^0-9]/g, "");
                fracPart = fracPart.replace(/[^0-9]/g, "");

                // Batasi 2 digit desimal
                if (fracPart.length > 2) fracPart = fracPart.slice(0, 2);

                return fracPart.length ? `${intPart},${fracPart}` : intPart;
            }

            // Validasi tampilan (boleh tanpa titik saat ngetik; titik baru saat blur)
            function isDisplayValid(v) {
                // valid jika: "123456", "123456,7", "123456,78" ATAU yg sudah terformat "1.234.567,89"
                return /^[0-9]+(,[0-9]{1,2})?$/.test(v) || /^[0-9]{1,3}(\.[0-9]{3})*(,[0-9]{1,2})?$/.test(v);
            }

            function showError(msg) {
                $err.text(msg).show();
                $biaya.addClass("is-invalid").attr("aria-invalid", "true");
            }

            function clearError() {
                $err.hide().text("");
                $biaya.removeClass("is-invalid").removeAttr("aria-invalid");
            }

            // 2) Saat ketik: hanya sanitasi (tanpa format ribuan). Tidak membatasi jumlah digit.
            $biaya.on("input", function() {
                const caret = this.selectionStart;
                const before = $biaya.val();
                const after = sanitizeLive(before);
                if (before !== after) {
                    $biaya.val(after);
                    // coba pertahankan caret kira-kira
                    const delta = before.length - after.length;
                    const newPos = Math.max(0, caret - Math.max(0, delta));
                    this.setSelectionRange(newPos, newPos);
                }
                if (!isDisplayValid(after)) {
                    showError("Format tidak valid (contoh: 1.000.000,25)");
                } else {
                    clearError();
                }
            });

            // 3) Saat paste: paksa lewat sanitizer
            $biaya.on("paste", function(e) {
                e.preventDefault();
                const t = (e.originalEvent || e).clipboardData.getData("text/plain") || "";
                $biaya.val(sanitizeLive(t)).trigger("input");
            });

            // 4) Saat focus: hilangkan titik ribuan jika ada (biar enak edit)
            $biaya.on("focus", function() {
                const v = $biaya.val().replace(/\./g, "");
                $biaya.val(v);
                clearError();
            });

            // 5) Saat blur: baru format ribuan
            $biaya.on("blur", function() {
                let v = $biaya.val();
                if (!v) return;
                v = sanitizeLive(v); // pastikan bersih
                const hasComma = v.includes(",");
                let [i, f = ""] = v.split(",");
                const iFmt = formatIntID(i || "0"); // tambah ribuan
                const final = hasComma ? `${iFmt},${f}` : iFmt;
                $biaya.val(final);

                if (!isDisplayValid(final)) {
                    showError("Format tidak valid (contoh: 1.000.000,25)");
                } else {
                    clearError();
                }
            });

            // 6) Cek terakhir sebelum submit
            $("#woForm").on("submit", function() {
                const v = $biaya.val().trim();
                if (v && !isDisplayValid(v)) {
                    showError("Format tidak valid (contoh: 1.000.000,25)");
                    $("html,body").animate({
                        scrollTop: $biaya.offset().top - 120
                    }, 300);
                    return false;
                }
                return true;
            });
        });
    </script>








    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
