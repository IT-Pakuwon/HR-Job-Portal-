{{-- resources/views/pages/wos/editwos.blade.php --}}
<x-app-layout>
    <x-spp-form-styles />
    <x-spp-form-select2 />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== EDIT FORM ====== --}}
                <form id="woForm" class="flex flex-col gap-4" enctype="multipart/form-data"
                    action="{{ route('wos.update', $prefill['hash']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="flex w-full flex-col gap-3 overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <!-- Header -->
                        <div class="-mx-6 -mt-6 flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 class="text-base font-bold text-gray-800 dark:text-white">✏️ Edit WO —
                                {{ $prefill['woid'] }}</h2><p class="text-xs text-gray-500 dark:text-gray-400">Fill in the work order details and attach the supporting files.</p></div></div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">

                            <!-- Company -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <select name="cpnyid" id="cpnyid" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $prefill['cpnyid'] ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- ✅ Business Unit -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business
                                    Unit</label>
                                <select name="business_unit_id" id="business_unit_id" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select name="departementid" id="departementid" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $prefill['departementid'] ? 'selected' : '' }}>
                                            {{ $p->department_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- WO Type -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">WO Type</label>
                                <select name="wotype" id="wotype" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">Select</option>
                                </select>
                            </div>

                            <!-- WO Request -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">WO
                                    Request</label>
                                <select name="worequest" id="worequest" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                            <!-- Jenis Pekerjaan -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis
                                    Pekerjaan</label>
                                <div class="flex items-stretch">
                                    <input id="jenis_pekerjaan_display" readonly
                                        class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Pilih Worktype & Subworktype">
                                    {{-- <button id="btnJenisPekerjaan" type="button"
                                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Pilih</button> --}}
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-white text-gray-500 transition hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                </div>
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <!-- Lokasi -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Lokasi</label>
                                <div class="flex items-stretch">
                                    <input id="lokasi_display" readonly
                                        class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Pilih Location & Sub Location">
                                    {{-- <button id="btnLokasi" type="button"
                                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Pilih</button> --}}
                                    <button type="button" id="btnLokasi"
                                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-white text-gray-500 transition hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                                </div>
                                <input type="hidden" name="location_id" id="location_id">
                                <input type="hidden" name="sub_location_id" id="sub_location_id">
                            </div>

                        </div>

                        <!-- Row 2 -->
                        {{-- <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                            <!-- Lokasi -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                                <div class="flex items-stretch">
                                    <input id="lokasi_display" readonly
                                        class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Pilih Location & Sub Location">
                                    <button type="button" id="btnLokasi"
                                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Pilih</button>
                                </div>
                                <input type="hidden" name="location_id" id="location_id">
                                <input type="hidden" name="sub_location_id" id="sub_location_id">
                            </div>

                            <!-- Jenis Pekerjaan -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">Jenis
                                    Pekerjaan</label>
                                <div class="flex items-stretch">
                                    <input id="jenis_pekerjaan_display" readonly
                                        class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                        placeholder="Pilih Worktype & Subworktype">
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Pilih</button>
                                </div>
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <!-- PIC Requester -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">PIC
                                    Requester</label>
                                <input type="text" name="picrequester" id="picrequester"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                            </div>

                            <!-- Biaya WO -->
                            <div class="flex flex-col gap-1.5">
                                <label class=" text-sm  font-medium text-gray-700 dark:text-gray-300">Biaya WO</label>
                                <input type="number" step="0.01" min="0" name="biaya_wo" id="biaya_wo"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="0.00">
                            </div>
                        </div> --}}

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <!-- Perpost -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Perpost</label>
                                <select id="perpost" name="perpost"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    @php $year = now()->year; @endphp
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    <option value="{{ $year + 1 }}">{{ $year + 1 }}</option>
                                </select>
                            </div>
                            <!-- PIC Requester -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PIC
                                    Requester</label>
                                <input type="text" name="picrequester" id="picrequester"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    value="{{ auth()->user()->username }}" required>
                            </div>

                            <!-- Biaya WO -->
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Biaya WO</label>
                                <input type="text" name="biaya_wo" id="biaya_wo" inputmode="decimal"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    placeholder="0,00">
                                <small id="biaya_wo_error" class="text-red-600" style="display:none;"></small>
                            </div>

                            <!-- Budget -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Budget</label>
                                <select name="wobudget" id="wobudget" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    <option value="">Select</option>
                                    <option value="Pemberi Kerja">Pemberi Kerja</option>
                                    <option value="Penerima Kerja">Penerima Kerja</option>
                                </select>
                            </div>

                        </div>
                        <!-- COA -->
                        <div id="coaGroup">
                            <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">COA</label>
                            <div class="flex items-stretch">
                                <input type="text" id="budget_display" readonly
                                    class="h-10 min-w-0 flex-1 rounded-l-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                                    placeholder="Pilih Budget">
                                <button type="button" id="btnBudget"
                                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-white text-gray-500 transition hover:bg-blue-600 hover:text-white dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    title="Lookup"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></button>
                            </div>

                            <!-- hidden -->
                            <input type="hidden" name="activity_id" id="activity_id">
                            <input type="hidden" name="coa_business_unit_id" id="coa_business_unit_id">
                            <input type="hidden" name="department_fin_id" id="department_fin_id">
                            <input type="hidden" name="coa_id" id="coa_id">
                            <input type="hidden" name="activity_descr" id="activity_descr">
                        </div>

                        <!-- Description -->
                        <div class="flex flex-col gap-1.5">
                            <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Description</label>
                            <textarea name="keperluan" id="keperluan" rows="2" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea>
                        </div>

                    </div>

                    {{-- ===== Modal Jenis Pekerjaan ===== --}}
                    <div id="modalJenisPekerjaan"
                        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-[95vw] max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Jenis Pekerjaan
                                </h3>
                                <button type="button" id="closeJenisPekerjaan"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sub
                                        Worktype</label>
                                    <select id="modal_subworktypeid"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="-mx-6 -mb-6 mt-6 flex justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                <button type="button" id="cancelJenisPekerjaan"
                                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                                <button type="button" id="saveJenisPekerjaan"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Save</button>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Modal Lokasi ===== --}}
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-[95vw] max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                    <select id="modal_location_id"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sub
                                        Location</label>
                                    <select id="modal_sub_location_id"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="-mx-6 -mb-6 mt-6 flex justify-end gap-3 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                                <button type="button" id="cancelLokasi"
                                    class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Cancel</button>
                                <button type="button" id="saveLokasi"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Save</button>
                            </div>
                        </div>
                    </div>

                    <div id="coaModal"
                        class="sf-modal fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-full max-w-4xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Select COA</h3>
                                <button type="button" id="closeCoaModal"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                                <input id="coaSearch" type="text" placeholder="Search code/name..."
                                    class="h-10 w-64 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                                <button id="coaRefresh" type="button"
                                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" title="Refresh"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg></button>
                                <div class="ml-auto flex items-center gap-3">
                                    <span>Company: <b id="coaCpnyBadge"></b></span>
                                    <span>Dept: <b id="coaDeptBadge"></b></span>
                                    <span>Perpost: <b id="coaPerpostBadge"></b></span>
                                </div>
                            </div>

                            <div class="max-h-[60vh] overflow-auto rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-sm dark:bg-gray-900">
                                        <tr>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Account ID</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Activity</th>
                                            <th class="border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400">Remaining Budget</th>
                                            <th class="w-24 border-b border-gray-200 px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:border-gray-700 dark:text-gray-400 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="coaTableBody" class="text-sm"></tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                <span id="coaCount" class="opacity-80"></span>
                                <div class="space-x-2">
                                    <button id="coaPrev" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Prev</button>
                                    <button id="coaNext" type="button"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Next</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ===== Attachments ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span><span class="req block text-base font-bold text-gray-800 dark:text-white">Attachments</span><span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this work order</span></span></span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="flex flex-col gap-2 px-6 pt-5">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/40 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="block truncate font-medium text-blue-700 hover:underline dark:text-blue-300">
                                                        {{ $att->display_name }}
                                                    </a>
                                                @else
                                                    <span
                                                        class="block truncate font-medium text-gray-700 dark:text-gray-200">
                                                        {{ $att->display_name }} (no link)
                                                    </span>
                                                @endif
                                                <div class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                                    {{ strtoupper($att->extention ?? '-') }}
                                                    @if (!empty($att->size))
                                                        • {{ number_format($att->size / 1024, 0) }} KB
                                                    @endif
                                                    @if (!empty($att->created_at))
                                                        •
                                                        {{ \Carbon\Carbon::parse($att->created_at)->format('d M Y H:i') }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="removeAttachment2 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                            aria-label="Remove attachment"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                @empty
                                    <div
                                        class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                                        No existing attachments.
                                    </div>
                                @endforelse
                            </div>

                            {{-- Upload baru --}}
                            <div id="attachmentsContainer" class="mt-6">
                                <div class="attachment-row flex items-center gap-2">
                                    <input type="file" name="attachments[]"
                                        class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                    <button type="button"
                                        class="removeAttachment hidden inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mx-6 mb-6 mt-4 flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Add Attachment
                            </button>
                        </details>


                        <div class="flex w-full justify-end gap-4 pt-4">
                            <button type="button" id="cancelBtn"
                                class="flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-5 py-2.5 text-sm font-medium text-red-700 shadow-sm transition hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <span id="btnText">Submit Approval</span>
                                <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                WO Updated Successfully!
            </div>
        </div>
    </div>

    {{-- Overlay --}}
    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing <span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    {{-- Helpers Overlay --}}
    <script>
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    {{-- ====== Prefill + Categories (WO Type/Request) ====== --}}
    <script>
        $(function() {
            const prefill = @json($prefill ?? []);

            // pilih option berdasarkan teks (karena value di kategori = text)
            function selectByText($sel, text) {
                if (!text) return;
                const $opt = $sel.find('option').filter(function() {
                    return $(this).text().trim() === String(text).trim();
                });
                if ($opt.length) {
                    $sel.val($opt.val());
                }
            }

            // Load kategori, lalu set selected ke teks prefill
            function loadCategories($select, categoryid, selectedText = null) {
                $select.html('<option value="">Select</option>');
                $.getJSON(`/wos/ajax/categories/${categoryid}`)
                    .done(function(list) {
                        list.forEach(it => $select.append(new Option(it.text, it.text)));
                        if (selectedText) {
                            selectByText($select, selectedText);
                        }
                        $select.trigger('change.select2');
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat kategori ' + categoryid);
                    });
            }
// styled dropdowns (Select2); refreshed via change.select2 after options are loaded
            $("#wotype, #worequest, #wobudget").each(function() {
                $(this).parent().addClass("sf-field");
                $(this).select2({ width: "100%", minimumResultsForSearch: 8, dropdownCssClass: "sf-s2-dropdown" });
            });

            loadCategories($('#wotype'), 'wotype', prefill.wotype || '');
            loadCategories($('#worequest'), 'worequest', prefill.worequest || '');

            // Prefill header
            $('select[name="cpnyid"]').val(prefill.cpnyid || '');
            $('select[name="departementid"]').val(prefill.departementid || '');
            $('#picrequester').val(prefill.picrequester || '');
            if (prefill.biaya_wo !== null && prefill.biaya_wo !== undefined) $('#biaya_wo').val(prefill.biaya_wo);
            $('#keperluan').val(prefill.keperluan || '');

            // Prefill lokasi
            $('#location_id').val(prefill.location_id || '');
            $('#sub_location_id').val(prefill.sub_location_id || '');
            const locDisp = [prefill.location_name || '', prefill.sub_location_name || ''].filter(Boolean).join(
                ' — ');
            if (locDisp) $('#lokasi_display').val(locDisp);

            // Prefill jenis pekerjaan
            $('#worktypeid').val(prefill.worktypeid || '');
            $('#subworktypeid').val(prefill.subworktypeid || '');
            const jpDisp = [prefill.worktype_name || '', prefill.subworktype_name || ''].filter(Boolean).join(
                ' — ');
            if (jpDisp) $('#jenis_pekerjaan_display').val(jpDisp);

            // Reset dependents jika header berubah
            $('select[name="cpnyid"]').on('change', () => $('#location_id, #sub_location_id, #lokasi_display').val(
                ''));
            $('select[name="departementid"]').on('change', () => $(
                '#worktypeid, #subworktypeid, #jenis_pekerjaan_display').val(''));
            $('select[name="cpnyid"]').on('change', () => {
                $('#location_id, #sub_location_id, #lokasi_display').val('');
                // reset COA
                $('#budget_display, #activity_id, #business_unit_id, #department_fin_id, #coa_id, #activity_descr')
                    .val('');
            });

            $('select[name="departementid"]').on('change', () => {
                $('#worktypeid, #subworktypeid, #jenis_pekerjaan_display').val('');
                // reset COA
                $('#budget_display, #activity_id, #business_unit_id, #department_fin_id, #coa_id, #activity_descr')
                    .val('');
            });



            // Prefill budget + perpost
            $('#wobudget').val(prefill.budget_use || '').trigger('change');
            if (prefill.perpost) $('#perpost').val(prefill.perpost);

            console.log('Budget prefill:', prefill.budget_use);
            console.log('Budget select value:', $('#wobudget').val());


            // Prefill COA (kalau ada)
            $('#activity_id').val(prefill.activity_id || '');
            $('#business_unit_id').val(prefill.business_unit_id || '');
            $('#department_fin_id').val(prefill.department_fin_id || '');
            $('#coa_id').val(prefill.coa_id || '');
            $('#activity_descr').val(prefill.activity_descr || '');

            // ===== tampilkan budget text di display (ACCOUNT_ID harus muncul) =====
            const accountId = prefill.coa_id || prefill.account_id || ''; // <-- ini yg benar
            const actDescr = prefill.activity_descr || prefill.act_descr || ''; // deskripsi activity

            if (prefill.coa_display) {
                // kalau controller kasih string siap tampil (pastikan formatnya ACCOUNT_ID — ACTIVITY)
                $('#budget_display').val(prefill.coa_display);
            } else {
                // fallback: ACCOUNT_ID — ACTIVITY_DESCR
                const text = [accountId, actDescr].filter(Boolean).join(' — ');
                $('#budget_display').val(text);
            }


            // toggleCoaSection();


        });
    </script>

    {{-- ===== Modal Jenis Pekerjaan (worktype/subworktype) ===== --}}
    <script>
        $(function() {
            const $dept = $('select[name="departementid"]');

            function openJenisModal() {
                $('#modalJenisPekerjaan').removeClass('hidden').addClass('flex');
            }

            function closeJenisModal() {
                $('#modalJenisPekerjaan').addClass('hidden').removeClass('flex');
            }

            // buka modal & load worktypes
            $('#btnJenisPekerjaan').on('click', function() {
                const params = $.param({
                    departementid: $dept.val() || ''
                });
                $('#modal_worktypeid').html('<option value="">Select</option>');
                $('#modal_subworktypeid').html('<option value="">Select</option>');
                $.getJSON(`/wos/ajax/worktypes?${params}`)
                    .done(function(list) {
                        list.forEach(it => $('#modal_worktypeid').append(new Option(it.text, it
                            .value)));
                        openJenisModal();
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Worktype.');
                    });
            });

            $('#closeJenisPekerjaan, #cancelJenisPekerjaan').on('click', closeJenisModal);

            // when worktype selected → load subworktypes (doctype=WO)
            $('#modal_worktypeid').on('change', function() {
                const wt = $(this).val();
                const $sub = $('#modal_subworktypeid');
                $sub.html('<option value="">Select</option>');
                if (!wt) return;
                $.getJSON(`/wos/ajax/subworktypes/${encodeURIComponent(wt)}?doctype=WO`)
                    .done(function(list) {
                        list.forEach(it => $sub.append(new Option(it.text, it.value)));
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Sub Worktype.');
                    });
            });

            // save to hidden + display
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

    {{-- ===== Modal Lokasi (location/sub-location) ===== --}}
    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $modal = $('#modalLokasi');
            const $selLoc = $('#modal_location_id');
            const $selSub = $('#modal_sub_location_id');

            function ensureSelect2() {
                if ($selLoc.hasClass('select2-hidden-accessible')) $selLoc.select2('destroy');
                if ($selSub.hasClass('select2-hidden-accessible')) $selSub.select2('destroy');
                $selLoc.select2({ dropdownParent: $modal, width: '100%', placeholder: 'Select', allowClear: true });
                $selSub.select2({ dropdownParent: $modal, width: '100%', placeholder: 'Select', allowClear: true });
            }

            function fillSelect($sel, list, selectedVal) {
                $sel.empty().append('<option value=""></option>');
                (list || []).forEach(it => $sel.append(new Option(it.text, it.value)));
                $sel.val(selectedVal || null);
                if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change.select2');
            }

            function loadSubLocations(loc) {
                const cpny = $cpny.val();
                fillSelect($selSub, [], null);
                if (!cpny || !loc) return;
                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`, function(list) {
                    fillSelect($selSub, list, null);
                }).fail(() => toastr.error('Gagal memuat Sub Location.'));
            }

            function openLokasiModal() {
                const cpny = $cpny.val();
                if (!cpny) { toastr.error('Pilih Company terlebih dahulu.'); return; }

                $modal.removeClass('hidden').addClass('flex');
                ensureSelect2();

                fillSelect($selLoc, [], null);
                fillSelect($selSub, [], null);

                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`, function(list) {
                    fillSelect($selLoc, list, null);
                    setTimeout(() => $selLoc.select2('open'), 100);
                }).fail(() => toastr.error('Gagal memuat Location.'));
            }

            function closeLokasiModal() {
                $modal.addClass('hidden').removeClass('flex');
            }

            $('#btnLokasi').on('click', openLokasiModal);
            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            // Use select2:select/clear — only fires on real user interaction, not programmatic trigger('change.select2')
            $selLoc.on('select2:select', function(e) {
                loadSubLocations(e.params.data.id);
            });
            $selLoc.on('select2:clear', function() {
                fillSelect($selSub, [], null);
            });

            $('#saveLokasi').on('click', function() {
                const locVal = $selLoc.val();
                const locTxt = $selLoc.find('option:selected').text();
                const subVal = $selSub.val();
                const subTxt = $selSub.find('option:selected').text();
                if (!locVal || !subVal) { toastr.error('Pilih Location dan Sub Location.'); return; }
                $('#location_id').val(locVal);
                $('#sub_location_id').val(subVal);
                $('#lokasi_display').val(`${locTxt} — ${subTxt}`);
                closeLokasiModal();
            });

            $cpny.on('change', function() {
                $('#location_id, #sub_location_id, #lokasi_display').val('');
            });
        });
    </script>

    {{-- ====== Submit + Validasi ====== --}}
    <script>
        $(function() {
            function clearErrors(scope = '#woForm') {
                $(scope).find('.is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $(scope).find('.error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) $el.after('<small class="error-feedback">' + msg +
                    '</small>');
            }

            $('#woForm').on('submit', function(e) {
                e.preventDefault();
                clearErrors();

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
                const $budget = $('#wobudget');
                const $coaDisp = $('#budget_display');
                const $coaId = $('#coa_id');
                const $activityId = $('#activity_id');

                let ok = true;

                // =========================
                // Attachment validation (same style as SPPB)
                // =========================
                let hasAttachment = false;

                $('#attachmentsContainer input[type="file"]').each(function() {
                    if (this.files && this.files.length > 0) {
                        hasAttachment = true;
                        return false;
                    }
                });

                if (!hasAttachment) {
                    const $firstFile = $('#attachmentsContainer input[type="file"]').first();
                    addError($firstFile, 'Minimal 1 attachment wajib diupload.');
                    ok = false;
                }
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

                // Budget wajib (sudah ada required di select, tapi kita enforce di JS juga)
                if (!$budget.val()) {
                    addError($budget, 'Budget wajib.');
                    ok = false;
                }

                // Jika Pemberi Kerja => COA wajib
                if (($budget.val() || '').toString().trim() === 'Pemberi Kerja') {
                    // minimal salah satu identitas COA harus ada
                    if (!$coaId.val() && !$activityId.val()) {
                        addError($coaDisp, 'COA wajib diisi untuk Pemberi Kerja.');
                        ok = false;
                    }
                }


                if (!ok) {
                    toastr.error('Mohon lengkapi input yang wajib.');
                    const $first = $('#woForm .is-invalid').first();
                    if ($first.length) $('html,body').animate({
                        scrollTop: $first.offset().top - 120
                    }, 300);
                    return;
                }

                $('#submitBtn, #cancelBtn').prop('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Submitting');

                const form = document.getElementById('woForm');
                const formData = new FormData(form);
                formData.set('_method', 'PUT');

                $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false
                    })
                    .done(function(res) {
                        toastr.success(res.message || "WO updated successfully!");
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
                        $('#submitBtn, #cancelBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        hideOverlay();
                    });
            });

            // Cancel
            $('#cancelBtn').on('click', function() {
                if (confirm('Batalkan perubahan? Perubahan belum disimpan akan hilang.')) {
                    if (document.referrer) window.history.back();
                    else window.location.href = "/wos";
                }
            });

            $(document).on('change', '#attachmentsContainer input[type="file"]', function() {
                if (this.files.length > 0) {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>

    {{-- ===== Attachment add/remove ===== --}}
    <script>
        $(function() {
            function toggleDeleteButton() {
                if ($('.attachment-row').length > 1) $('.removeAttachment').removeClass('hidden');
                else $('.removeAttachment').addClass('hidden');
            }

            $('#addAttachment').on('click', function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2">
                        <input type="file" name="attachments[]" class="mt-2 flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                        <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                    </div>
                `);
                toggleDeleteButton();
            });

            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteButton();
            });

            toggleDeleteButton();
        });
    </script>
    <script>
        $(document).on('click', '.removeAttachment2', function() {
            const $btn = $(this);
            const $row = $btn.closest('.attachment-row');
            const attachmentId = $row.data('id');

            if (!attachmentId) {
                toastr.error('Attachment ID tidak ditemukan.');
                return;
            }

            if (!confirm('Are you sure you want to remove this attachment?')) return;

            // lock UI kecil pada tombol
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html(`
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                Removing...
            `);

            $.ajax({
                    url: "/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    }
                })
                .done(function(res) {
                    if (res && res.success) {
                        // animasi keluar biar halus
                        $row.slideUp(180, function() {
                            $(this).remove();
                        });
                        toastr.success('Attachment removed.');
                    } else {
                        toastr.error(res?.message || 'Failed to remove attachment.');
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                })
                .fail(function(xhr) {
                    toastr.error('Error! Unable to remove attachment.');
                    console.error(xhr.responseText);
                    $btn.prop('disabled', false).html(originalHtml);
                });
        });
    </script>

    <script>
        function toggleCoaSection() {
            const budgetVal = ($('#wobudget').val() || '').toString().trim(); // Pemberi Kerja/External
            const $coaGroup = $('#coaGroup');

            if (budgetVal === 'Pemberi Kerja') {
                $coaGroup.removeClass('hidden');
                $('#btnBudget').prop('disabled', false);
            } else {
                $coaGroup.addClass('hidden');

                // clear COA fields
                $('#budget_display').val('');
                $('#activity_id').val('');
                $('#coa_business_unit_id').val('');
                $('#department_fin_id').val('');
                $('#coa_id').val('');
                $('#activity_descr').val('');


                $('#budget_display').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#budget_display').next('.error-feedback').remove();
            }
        }

        $(function() {
            // apply saat load (setelah prefill value terpasang)
            toggleCoaSection();

            // apply saat user ganti budget
            $('#wobudget').on('change', toggleCoaSection);
        });
    </script>

    <script>
        $(function() {
            const prefill = @json($prefill ?? []);

            const $cpny = $('#cpnyid');
            const $bu = $('#business_unit_id');

            function renderBuOptions(list, selected) {
                let html = '<option value="" disabled>Select Business Unit</option>';
                (list || []).forEach(it => {
                    const id = it.business_unit_id ?? it.businessunit_id ?? '';
                    const name = it.business_unit_name ?? it.businessunit_name ?? id;
                    const sel = (selected && String(selected) === String(id)) ? 'selected' : '';
                    html += `<option value="${id}" ${sel}>${id} - ${$('<div>').text(name).html()}</option>`;
                });
                return html;
            }

            function loadBusinessUnitsByCpny(cpnyid, selected = null) {
                if (!cpnyid) {
                    $bu.html('<option value="" disabled selected>Select Company first</option>');
                    return;
                }

                $bu.html('<option value="" disabled selected>Loading...</option>');

                $.getJSON("{{ route('businessunits.byCpny') }}", {
                        cpnyid
                    })
                    .done(function(res) {
                        const list = res.data || [];
                        if (!list.length) {
                            $bu.html('<option value="" disabled selected>No Business Unit</option>');
                            return;
                        }

                        $bu.html(renderBuOptions(list, selected));

                        // kalau selected kosong, auto pilih pertama
                        if (!selected) $bu.val(list[0].business_unit_id);
                    })
                    .fail(function() {
                        $bu.html('<option value="" disabled selected>Failed to load</option>');
                    });
            }

            // initial load berdasarkan company prefill, dan pilih BU prefill
            loadBusinessUnitsByCpny($cpny.val(), prefill.business_unit_id || null);

            // change company -> reload BU + reset COA
            $cpny.on('change', function() {
                loadBusinessUnitsByCpny($(this).val(), null);

                // reset COA ketika company berubah
                $('#budget_display, #activity_id, #department_fin_id, #coa_id, #activity_descr').val('');
            });

            // change BU -> reset COA (karena COA tergantung BU)
            $bu.on('change', function() {
                $('#budget_display, #activity_id, #department_fin_id, #coa_id, #activity_descr').val('');
            });
        });
    </script>

    <script>
        $(function() {
            // pastikan element ada
            console.log('btnBudget:', $('#btnBudget').length, 'coaModal:', $('#coaModal').length);

            const $coaModal = $('#coaModal');
            const $btnBudget = $('#btnBudget');

            function openCoaModal() {
                const cpny = $('#cpnyid').val() || $('select[name="cpnyid"]').val();
                const dept = $('#departementid').val() || $('select[name="departementid"]').val();
                const buid = $('#business_unit_id').val();
                const perpost = $('#perpost').val();

                if (!cpny) return toastr.warning('Pilih Company terlebih dahulu.');
                if (!dept) return toastr.warning('Pilih Department terlebih dahulu.');
                if (!buid) return toastr.warning('Pilih Business Unit terlebih dahulu.');

                // tampilkan modal
                $coaModal.removeClass('hidden').addClass('flex');

                // TODO: panggil loadCoa() kamu disini
                // loadCoa({cpnyid: cpny, deptid: dept, perpost, business_unit_id: buid});
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            // bind click
            $btnBudget.off('click').on('click', openCoaModal);

            // close
            $('#closeCoaModal').off('click').on('click', closeCoaModal);
        });
    </script>

    {{-- <script>
        $(function () {
            const prefill = @json($prefill) || {};

            // Elements
            const $coaModal = $('#coaModal');
            const $tbody    = $('#coaTableBody');
            const $search   = $('#coaSearch');
            const $count    = $('#coaCount');
            const $prev     = $('#coaPrev');
            const $next     = $('#coaNext');

            // badges
            const $bCpny    = $('#coaCpnyBadge');
            const $bDept    = $('#coaDeptBadge');
            const $bPerpost = $('#coaPerpostBadge');

            // state paging
            let state = {
                page: 1,
                per_page: 10,
                total: 0,
                search: ''
            };

            function esc(s){ return $('<div>').text(s ?? '').html(); }
            function fmtNum(n){
                if (n === null || n === undefined || n === '') return '';
                const x = Number(n);
                if (isNaN(x)) return esc(n);
                return x.toLocaleString('id-ID');
            }

            function getHeaderParams() {
                return {
                    cpnyid: $('#cpnyid').val(),
                    deptid: $('#departementid').val(),
                    perpost: $('#perpost').val(),
                    business_unit_id: $('#business_unit_id').val(), // ✅ kirim BU ke backend
                };
            }

            function openCoaModal() {
                const p = getHeaderParams();

                if (!p.cpnyid) return toastr.warning('Pilih Company terlebih dahulu.');
                if (!p.deptid) return toastr.warning('Pilih Department terlebih dahulu.');
                if (!p.business_unit_id) return toastr.warning('Pilih Business Unit terlebih dahulu.');
                if (!p.perpost) return toastr.warning('Pilih Perpost terlebih dahulu.');

                // set badge
                $bCpny.text(p.cpnyid);
                $bDept.text(p.deptid);
                $bPerpost.text(p.perpost);

                // show modal
                $coaModal.removeClass('hidden').addClass('flex');

                // load data
                state.page = 1;
                state.search = $search.val().trim();
                loadCoa();
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            function renderRows(rows) {
                if (!rows || !rows.length) {
                    $tbody.html(`<tr><td colspan="4" class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">No data</td></tr>`);
                    return;
                }

                const html = rows.map(r => {
                    const accountId = r.account_id ?? '';
                    const accountDescr = r.account_descr ?? '';
                    const activityId = r.activity_id ?? '';
                    const activityDescr = r.activity_descr ?? r.act_descr ?? '';
                    const remaining = r.remaining ?? '';

                    // data yang harus kita simpan ke hidden saat pilih
                    const deptFin = r.department_fin_id ?? '';
                    const buid = r.business_unit_id ?? '';

                    const actLabel = [
                        activityId,
                        activityDescr
                    ].filter(Boolean).join(' — ');

                    const accLabel = [
                        accountId,
                        accountDescr
                    ].filter(Boolean).join(' — ');

                    return `
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(accLabel)}</td>
                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(actLabel)}</td>
                        <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(fmtNum(remaining))}</td>
                        <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                            <button type="button"
                                class="coaPick inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white"
                                data-account_id="${esc(accountId)}"
                                data-account_descr="${esc(accountDescr)}"
                                data-activity_id="${esc(activityId)}"
                                data-activity_descr="${esc(activityDescr)}"
                                data-department_fin_id="${esc(deptFin)}"
                                data-business_unit_id="${esc(buid)}"
                            >Select</button>
                        </td>
                    </tr>
                    `;
                }).join('');

                $tbody.html(html);
            }

            function updatePager() {
                const totalPages = Math.max(1, Math.ceil((state.total || 0) / state.per_page));
                $prev.prop('disabled', state.page <= 1);
                $next.prop('disabled', state.page >= totalPages);
                $count.text(`Page ${state.page} / ${totalPages} • Total ${state.total || 0}`);
            }

            function loadCoa() {
                const p = getHeaderParams();

                // 🔥 endpoint backend kamu (sesuaikan route kalau berbeda)
                // kamu minta "tambahkan business_unit_id pada CoaBudgetbyDept"
                // jadi endpoint ini harus mengarah ke function itu
                const url = "{{ route('coa.byDeptWo') }}"; // <-- sesuaikan nama route kamu

                $tbody.html(`<tr><td colspan="4" class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>`);

                $.getJSON(url, {
                    cpnyid: p.cpnyid,
                    deptid: p.deptid,
                    perpost: p.perpost,
                    business_unit_id: p.business_unit_id,   // ✅ penting
                    search: state.search,
                    page: state.page,
                    per_page: state.per_page
                })
                .done(function(res){
                    const rows = res.data || [];
                    state.total = Number(res.total || 0);

                    renderRows(rows);
                    updatePager();
                })
                .fail(function(xhr){
                    console.error(xhr.responseText);
                    $tbody.html(`<tr><td colspan="4" class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700 text-center text-red-600">Failed load COA</td></tr>`);
                    toastr.error('Gagal load COA.');
                });
            }

            // =========================
            // Events
            // =========================
            $('#btnBudget').off('click').on('click', openCoaModal);
            $('#closeCoaModal').off('click').on('click', closeCoaModal);

            $('#coaRefresh').on('click', function(){
                state.page = 1;
                state.search = $search.val().trim();
                loadCoa();
            });

            let t = null;
            $search.on('input', function(){
                clearTimeout(t);
                t = setTimeout(function(){
                    state.page = 1;
                    state.search = $search.val().trim();
                    loadCoa();
                }, 350);
            });

            $prev.on('click', function(){
                if (state.page <= 1) return;
                state.page -= 1;
                loadCoa();
            });

            $next.on('click', function(){
                const totalPages = Math.max(1, Math.ceil((state.total || 0) / state.per_page));
                if (state.page >= totalPages) return;
                state.page += 1;
                loadCoa();
            });

            // pick row
            $(document).on('click', '.coaPick', function(){
                const $btn = $(this);

                const accountId = $btn.data('account_id') || '';
                const accountDescr = $btn.data('account_descr') || '';
                const activityId = $btn.data('activity_id') || '';
                const activityDescr = $btn.data('activity_descr') || '';
                const deptFin = $btn.data('department_fin_id') || '';
                const buid = $btn.data('business_unit_id') || '';

                // set hidden fields
                $('#coa_id').val(accountId);
                $('#activity_id').val(activityId);
                $('#activity_descr').val(activityDescr);
                $('#department_fin_id').val(deptFin);
                $('#coa_business_unit_id').val(buid);

                // display
                const disp = [
                    accountId,
                    accountDescr,
                    activityId,
                    activityDescr
                ].filter(Boolean).join(' — ');
                $('#budget_display').val(disp);

                closeCoaModal();
            });

            // reset COA if header changes
            function resetCoaFields(){
                $('#budget_display').val('');
                $('#activity_id').val('');
                $('#coa_business_unit_id').val('');
                $('#department_fin_id').val('');
                $('#coa_id').val('');
                $('#activity_descr').val('');
            }

            $('#cpnyid, #departementid, #business_unit_id, #perpost').on('change', function(){
                resetCoaFields();
            });

            // prefill COA hidden yg benar
            if (prefill && prefill.business_unit_id) {
                $('#coa_business_unit_id').val(prefill.business_unit_id);
            }
        });
    </script> --}}
    <script>
        $(function() {
            // ===== Prefill safe (tidak error kalau $prefill null) =====
            const prefill = @json($prefill ?? []);

            // ===== Elements =====
            const $coaModal = $('#coaModal');
            const $coaTbody = $('#coaTableBody');
            const $coaCount = $('#coaCount');
            const $coaCpny = $('#coaCpnyBadge');
            const $coaDept = $('#coaDeptBadge');
            const $coaPerpost = $('#coaPerpostBadge');
            const $btnBudget = $('#btnBudget');
            const $search = $('#coaSearch');

            // ===== State =====
            let coaState = {
                search: '',
                page: 1,
                per_page: 10,
                total: 0,
                cpnyid: null,
                deptid: null,
                perpost: null,
                buid: null,
            };

            function esc(s) {
                return $('<div>').text(s ?? '').html();
            }

            function formatNumber(n) {
                if (n === null || n === undefined || n === '') return '';
                const x = Number(n);
                if (isNaN(x)) return String(n);
                return x.toLocaleString('id-ID');
            }

            function getHeader() {
                return {
                    cpnyid: $('#cpnyid').val(),
                    deptid: $('#departementid').val(),
                    perpost: $('#perpost').val(),
                    buid: $('#business_unit_id').val(),
                };
            }

            function openCoaModal() {
                const h = getHeader();

                if (!h.cpnyid) {
                    toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!h.deptid) {
                    toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }
                if (!h.buid) {
                    toastr.warning('Pilih Business Unit terlebih dahulu.');
                    return;
                }
                if (!h.perpost) {
                    toastr.warning('Pilih Perpost terlebih dahulu.');
                    return;
                }

                coaState.cpnyid = h.cpnyid;
                coaState.deptid = h.deptid;
                coaState.perpost = h.perpost;
                coaState.buid = h.buid;
                coaState.page = 1;
                coaState.search = '';

                $coaCpny.text(coaState.cpnyid);
                $coaDept.text(coaState.deptid);
                $coaPerpost.text(coaState.perpost);
                $search.val('');

                $coaModal.removeClass('hidden').addClass('flex');
                loadCoa();
            }

            function closeCoaModal() {
                $coaModal.addClass('hidden').removeClass('flex');
            }

            // ===== Open/Close events =====
            $btnBudget.off('click').on('click', openCoaModal);
            $('#closeCoaModal').off('click').on('click', closeCoaModal);

            $(document).off('keydown.coa').on('keydown.coa', function(e) {
                if (e.key === 'Escape' && $coaModal.is(':visible')) closeCoaModal();
            });

            // ===== Search (debounce) + refresh =====
            let t = null;
            $search.off('input').on('input', function() {
                clearTimeout(t);
                t = setTimeout(function() {
                    coaState.search = $search.val().trim();
                    coaState.page = 1;
                    loadCoa();
                }, 350);
            });

            $('#coaRefresh').off('click').on('click', function() {
                $search.val('');
                coaState.search = '';
                coaState.page = 1;
                loadCoa();
            });

            // ===== Pagination =====
            $('#coaPrev').off('click').on('click', function() {
                if (coaState.page > 1) {
                    coaState.page--;
                    loadCoa();
                }
            });

            $('#coaNext').off('click').on('click', function() {
                const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                if (coaState.page < maxPage) {
                    coaState.page++;
                    loadCoa();
                }
            });

            // ===== Load COA =====
            function loadCoa() {
                $coaTbody.html('<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                $coaCount.text('');

                $.getJSON("{{ route('coa.byDeptWo') }}", {
                        cpnyid: coaState.cpnyid,
                        deptid: coaState.deptid,
                        perpost: coaState.perpost,
                        business_unit_id: coaState.buid, // ✅ kirim BU ke backend
                        search: coaState.search,
                        page: coaState.page,
                        per_page: coaState.per_page
                    })
                    .done(function(res) {
                        const data = res.data || [];
                        coaState.total = Number(res.total || 0);

                        if (!data.length) {
                            $coaTbody.html('<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        } else {
                            const rowsHtml = data.map(item => {
                                const accountId = item.account_id ?? item.coa_id ?? '';
                                const accountDesc = item.account_descr ?? '';
                                const actId = item.activity_id ?? '';
                                const actDescr = item.activity_descr ?? item.act_descr ?? '';

                                const buId = item.business_unit_id ?? '';
                                const deptFinId = item.department_fin_id ?? '';

                                const available = formatNumber(item.availablebudget ?? item
                                    .available_budget ?? '');
                                const used = formatNumber(item.usedbudget ?? item.used_budget ?? '');
                                const remaining = formatNumber(item.remaining ?? '');

                                // label display di input: ACCOUNT_ID — ACCOUNT_DESCR — ACTIVITY_ID — ACTIVITY_DESCR
                                const label = [
                                    accountId,
                                    accountDesc,
                                    actId,
                                    actDescr
                                ].filter(Boolean).join(' — ');

                                return `
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(accountId)}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${esc(actDescr)}</td>
                            <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                                <div class="font-semibold">${esc(remaining)}</div>
                                <div class="text-sm opacity-70">Available : ${esc(available)}</div>
                                <div class="text-sm opacity-70">Used : ${esc(used)}</div>
                            </td>
                            <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                                <button type="button"
                                    class="chooseCoa inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                    data-account_id="${esc(accountId)}"
                                    data-account_descr="${esc(accountDesc)}"
                                    data-activity_id="${esc(actId)}"
                                    data-activity_descr="${esc(actDescr)}"
                                    data-business_unit_id="${esc(buId)}"
                                    data-department_fin_id="${esc(deptFinId)}"
                                    data-label="${esc(label)}"
                                >Choose</button>
                            </td>
                        </tr>
                    `;
                            }).join('');

                            $coaTbody.html(rowsHtml);
                        }

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);

                        const shown = data.length;
                        $coaCount.text(
                            `Page ${coaState.page}/${maxPage} • Showing ${shown} of ${coaState.total} items`
                        );
                    })
                    .fail(function(xhr) {
                        console.error(xhr.responseText);
                        $coaTbody.html(
                            '<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
                        );
                        $coaCount.text('');
                        $('#coaPrev, #coaNext').prop('disabled', true);
                        toastr.error('Gagal load COA.');
                    });
            }

            // ===== Choose COA =====
            $(document).off('click.chooseCoa').on('click.chooseCoa', '.chooseCoa', function() {
                const $btn = $(this);

                const accountId = $btn.data('account_id') || '';
                const actId = $btn.data('activity_id') || '';
                const buId = $btn.data('business_unit_id') || '';
                const deptFinId = $btn.data('department_fin_id') || '';
                const actDescr = $btn.data('activity_descr') || '';
                const label = $btn.data('label') || '';

                // hidden fields (sesuai form kamu)
                $('#coa_id').val(accountId);
                $('#activity_id').val(actId);
                $('#coa_business_unit_id').val(buId);
                $('#department_fin_id').val(deptFinId);
                $('#activity_descr').val(actDescr);

                // display: pastikan ACCOUNT_ID tampil (bukan activity_id)
                $('#budget_display').val(label);

                // clear error
                $('#budget_display').removeClass('is-invalid').next('.error-feedback').remove();

                closeCoaModal();
            });

            // ===== Reset COA jika header berubah =====
            function resetCoaFields() {
                $('#budget_display').val('');
                $('#coa_id').val('');
                $('#activity_id').val('');
                $('#activity_descr').val('');
                $('#department_fin_id').val('');
                $('#coa_business_unit_id').val('');
            }

            $('#cpnyid, #departementid, #business_unit_id, #perpost').on('change', function() {
                resetCoaFields();

                // kalau modal sedang terbuka, auto reload sesuai header baru
                if ($coaModal.is(':visible')) {
                    const h = getHeader();
                    coaState.cpnyid = h.cpnyid;
                    coaState.deptid = h.deptid;
                    coaState.perpost = h.perpost;
                    coaState.buid = h.buid;
                    coaState.page = 1;
                    loadCoa();
                }
            });

            // ===== Prefill hidden aman (optional) =====
            if (prefill && prefill.business_unit_id) {
                $('#coa_business_unit_id').val(prefill.business_unit_id);
            }
        });
    </script>








    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    </style>
    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
