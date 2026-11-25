<x-app-layout>
    <x-spp-form-styles />
    <x-spp-form-select2 />
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="woForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    @if (!empty($complaint))
                        <input type="hidden" name="complaint_eid" value="{{ $complaint['eid'] }}">
                        <div class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                            Creating a WO from complaint <b>{{ $complaint['nbr'] }}</b>. The complaint becomes "Transferred to WO" once this WO is saved.
                        </div>
                    @endif
                    <div class="flex w-full flex-col gap-3 overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <!-- Header -->
                        <div class="-mx-6 -mt-6 flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span><div><h2 class="text-base font-bold text-gray-800 dark:text-white">Create WO</h2><p class="text-xs text-gray-500 dark:text-gray-400">Fill in the work order details and attach the supporting files.</p></div></div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-5">
                            <!-- Company -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                @if (!empty($complaint['cpny']))
                                    {{-- disabled selects are not submitted, so the locked value travels in a hidden input --}}
                                    <input type="hidden" name="cpnyid" value="{{ $complaint['cpny'] }}">
                                @endif
                                <select name="cpnyid" id="cpnyid" required @disabled(!empty($complaint['cpny']))
                                    class="{{ !empty($complaint['cpny']) ? 'cursor-not-allowed bg-gray-100 opacity-70 ' : '' }}sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    @php $lockCpny = strtoupper(trim($complaint['cpny'] ?? '')); @endphp
                                    @foreach ($usercpny as $p)
                                        {{-- From a complaint: company follows the complaint's site and cannot be changed --}}
                                        @continue($lockCpny !== '' && strtoupper(trim($p->cpny_id)) !== $lockCpny)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $lockCpny !== '' || $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Business
                                    Unit</label>
                                <select name="business_unit_id" id="business_unit_id"
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                                    required>
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>


                            <!-- Department -->
                            <div class="flex flex-col gap-1.5">
                                <label class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <select name="departementid" required
                                    class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $userdept2->department_id ? 'selected' : '' }}>
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

                        <!-- Row 2 -->
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

                        <!-- Row 3 -->
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
                                {{-- <button id="btnBudget" type="button"
                                    class="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">Pilih</button> --}}
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
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">{{ $complaint['text'] ?? '' }}</textarea>
                        </div>
                    </div>


                    <!-- Modal -->
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
                                <!-- Kiri: Worktype -->
                                <div>
                                    <label
                                        class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>

                                <!-- Kanan: Subworktype (dependent) -->
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

                    <!-- Modal Lokasi -->
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                        <div class="w-[95vw] max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="-mx-6 -mt-6 mb-5 flex items-center justify-between border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Kiri: Location -->
                                <div>
                                    <label
                                        class="req block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</label>
                                    <select id="modal_location_id"
                                        class="sf-select mt-1 h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">Select</option>
                                    </select>
                                </div>

                                <!-- Kanan: Sub Location (dependent) -->
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

                    <!-- ===== Modal Lookup COA ===== -->
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




                    {{-- ===== Attachment ===== --}}
                    <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span><span class="req block text-base font-bold text-gray-800 dark:text-white">Attachments</span><span class="block text-xs text-gray-500 dark:text-gray-400">Supporting documents for this work order</span></span></span>
                                <span class="text-sm font-medium text-blue-600 transition-all group-open:hidden">See details &rarr;</span>
                                <span class="hidden text-sm font-medium text-blue-600 transition-all group-open:inline">Hide details &darr;</span>
                            </summary>
                            <div class="flex flex-col px-6 pt-5">
                                @if (!empty($complaint['files']))
                                    <div class="mb-4 rounded-lg border border-violet-200 bg-violet-50 p-3 text-sm dark:border-violet-500/30 dark:bg-violet-500/10">
                                        <p class="mb-2 font-semibold text-violet-800 dark:text-violet-200">
                                            <i class="fa-solid fa-paperclip mr-1"></i>From complaint {{ $complaint['nbr'] }}
                                            ({{ count($complaint['files']) }}) - these files are attached to the WO automatically
                                        </p>
                                        <ul class="space-y-1">
                                            @foreach ($complaint['files'] as $f)
                                                <li><a href="{{ $f['url'] }}" target="_blank" class="text-violet-700 underline hover:text-violet-900 dark:text-violet-300">{{ $f['name'] }}</a></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment hidden inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mx-6 mb-6 mt-4 flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" style="width: calc(100% - 3rem);"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> Add Attachment
                            </button>
                        </details>

                        <div
                            class="flex flex-row items-center justify-between gap-4 border-t border-gray-100 bg-gray-50/60 px-6 py-4 dark:border-gray-700 dark:bg-white/[0.02]">
                            <button id="backBtn" onclick="history.back()"
                                class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">

                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>

                                <span>Back</span>
                            </button>

                            <div class="flex justify-start md:justify-end">
                                <button type="submit" id="submitBtn"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                $select.empty().append('<option value="">Select</option>');
                $.getJSON(`/wos/ajax/categories/${encodeURIComponent(categoryid)}?doctype=WO`)
                    .done(function(list) {
                        list.forEach(function(it) {
                            // value & text sama = category_name
                            $select.append(new Option(it.text, it.text));
                        });
                        $select.trigger('change.select2');
                        @if (!empty($complaint))
                            // From a complaint: WO Request is always "Tenant" (only that option is left, so it is still submitted).
                            if (categoryid === 'worequest') {
                                $select.find('option').filter(function() {
                                    return this.value.toLowerCase() !== 'tenant';
                                }).remove();
                                const v = $select.find('option').first().val();
                                $select.val(v).prop('disabled', true).addClass('cursor-not-allowed bg-gray-100 opacity-70');
                                // disabled selects are not submitted, so the value travels in a hidden input
                                $select.after($('<input type="hidden" name="worequest">').val(v));
                                $select.trigger('change.select2');
                            }
                        @endif
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat data kategori.');
                    });
            }

// styled dropdowns (Select2); refreshed via change.select2 after options are loaded
            $("#wotype, #worequest, #wobudget").each(function() {
                $(this).parent().addClass("sf-field");
                $(this).select2({ width: "100%", minimumResultsForSearch: 8, dropdownCssClass: "sf-s2-dropdown" });
            });

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
                $('#modal_worktypeid').empty().append('<option value="">Select</option>');
                $('#modal_subworktypeid').empty().append('<option value="">Select</option>');
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
            //     $sub.empty().append('<option value="">Select</option>');
            //     if (!wt) return;
            //     $.getJSON(`/wos/ajax/subworktypes/${encodeURIComponent(wt)}`, function(list){
            //     list.forEach(it => $sub.append(new Option(it.text, it.value)));
            //     });
            // });
            // ketika worktype dipilih → load subworktypes (doctype=WO)
            $('#modal_worktypeid').on('change', function() {
                const wt = $(this).val();
                const $sub = $('#modal_subworktypeid');
                $sub.empty().append('<option value="">Select</option>');
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

                // =========================
                // Attachment validation (same style as SPPB)
                // =========================
                // Files carried over from the source complaint already count as attachments.
                let hasAttachment = {{ !empty($complaint['files']) ? 'true' : 'false' }};

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
                if (!$('#business_unit_id').val()) {
                    addError($('#business_unit_id'), 'Business Unit wajib.');
                    ok = false;
                }
                if ($biaya.val() && isNaN(parseFloat($biaya.val()))) {
                    addError($biaya, 'Biaya WO tidak valid.');
                    ok = false;
                }

                const $wobudget = $('#wobudget');
                const needsCoa = $wobudget.val() === 'Pemberi Kerja';
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

            $(document).on('change', '#attachmentsContainer input[type="file"]', function() {
                if (this.files.length > 0) {
                    $(this).removeClass('is-invalid');
                }
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
                <input type="file" name="attachments[]" class="mt-2 flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                    <button type="button" class="removeAttachment inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-300"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
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
                $('#coa_id, #activity_id, #coa_business_unit_id, #department_fin_id, #activity_descr').val('');
                $('#budget_display').val('');
            }

            function applyBudgetVisibility() {
                const val = $wobudget.val();
                if (val === 'Pemberi Kerja') { // Pemberi Kerja
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
                buid: null,
            };

            function openCoaModal() {
                const cpny = $('select[name="cpnyid"]').val();
                const dept = $('select[name="departementid"]').val();
                const perpost = $('#perpost').val();
                const buid = $('#business_unit_id').val();


                if (!cpny) {
                    if (window.toastr) toastr.warning('Pilih Company terlebih dahulu.');
                    return;
                }
                if (!dept) {
                    if (window.toastr) toastr.warning('Pilih Department terlebih dahulu.');
                    return;
                }

                if (!buid) {
                    toastr.warning('Pilih Business Unit terlebih dahulu.');
                    return;
                }

                coaState.cpnyid = cpny;
                coaState.deptid = dept;
                coaState.perpost = perpost;
                coaState.buid = buid;
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
                $coaTbody.html('<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Loading...</td></tr>');
                $.getJSON("{{ route('coa.byDeptWo') }}", {
                        cpnyid: coaState.cpnyid,
                        deptid: coaState.deptid,
                        perpost: coaState.perpost,
                        business_unit_id: coaState.buid,
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
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${id}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">${actDetail}</td>
                    <td class="border-b border-gray-100 px-3 py-2.5 dark:border-gray-700">
                        <div class="font-semibold">${remaining}</div>
                        <div class=" text-sm  opacity-70">Available : ${available}</div>
                        <div class=" text-sm  opacity-70">Used: ${used}</div>
                    </td>
                    <td class="border-b border-gray-100 px-3 py-2.5 text-gray-700 dark:border-gray-700 dark:text-gray-200 text-center">
                        <button type="button" class="chooseCoa inline-flex items-center rounded-lg border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
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

                        $coaTbody.html(rows || '<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No data</td></tr>');
                        coaState.total = res.total || 0;
                        const shown = rows ? (res.data || []).length : 0;
                        $coaCount.text(`Showing ${shown} of ${coaState.total} items`);

                        const maxPage = Math.ceil((coaState.total || 0) / coaState.per_page) || 1;
                        $('#coaPrev').prop('disabled', coaState.page <= 1);
                        $('#coaNext').prop('disabled', coaState.page >= maxPage);
                    })
                    .fail(function() {
                        $coaTbody.html(
                            '<tr><td colspan="4" class="px-3 py-8 text-center text-sm text-red-600">Failed to load</td></tr>'
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
                $('#coa_business_unit_id').val(buId);
                $('#department_fin_id').val(deptFinId);
                $('#activity_descr').val(actDescr);
                $('#budget_display').val($('<div>').html(label).text()); // unescape label untuk display

                // bersihkan error jika ada
                $('#budget_display').removeClass('is-invalid').next('.error-feedback').remove();

                closeCoaModal();
            });

            // Jika cpny/dept/perpost berubah saat modal terbuka → refresh
            $('select[name="cpnyid"], select[name="departementid"], #perpost, #business_unit_id').on('change',
                function() {
                    if ($coaModal.is(':visible')) {
                        coaState.cpnyid = $('select[name="cpnyid"]').val();
                        coaState.deptid = $('select[name="departementid"]').val();
                        coaState.perpost = $('#perpost').val();
                        coaState.buid = $('#business_unit_id').val(); // ✅ NEW
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

    <script>
        $(function() {
            const $cpny = $('#cpnyid'); // ✅ sekarang ada
            const $bu = $('#business_unit_id'); // ✅ select BU

            function renderBuOptions(list, selected) {
                let html = '<option value="" disabled selected>Select Business Unit</option>';
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

                        // auto select pertama kalau belum ada selected
                        if (!selected) $bu.val(list[0].business_unit_id);
                    })
                    .fail(function() {
                        $bu.html('<option value="" disabled selected>Failed to load</option>');
                    });
            }

            // initial
            loadBusinessUnitsByCpny($cpny.val());

            // change
            $cpny.on('change', function() {
                loadBusinessUnitsByCpny($(this).val());
            });
        });
    </script>



    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container { width: 100% !important; }
        .select2-container .select2-selection--single { height: 42px; border: 1px solid #d1d5db; border-radius: 0.5rem; display: flex; align-items: center; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    </style>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</x-app-layout>
