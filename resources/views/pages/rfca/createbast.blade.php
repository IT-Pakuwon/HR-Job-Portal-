<x-app-layout>



    {{-- ===== Overlay styles ===== --}}


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="bastForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    {{-- penting: hash id term untuk store --}}
                    <input type="hidden" name="term_eid" value="{{ $term_eid }}">
                    <input type="hidden" name="ponbr" value="{{ $term->ponbr }}">

                    {{-- ===== Header ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Create Bast</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Check the reference details, then fill in the quantities and attachments.</p>
                            </div>
                        </div>

                        {{-- Row 1 (5 kolom) --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">PO Nbr</label>
                                <input type="text" value="{{ $term->ponbr }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                                <input type="text" value="{{ $term->cpny_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CS ID</label>
                                <input type="text" value="{{ $term->csid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SPPB/J/K/T</label>
                                <input type="text" value="{{ $term->sppbjktid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                                <input type="text" value="{{ $term->department_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 2 (5 kolom) --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">User
                                    Peminta</label>
                                <input type="text" value="{{ $term->user_peminta }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label
                                    class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Keperluan</label>
                                <input type="text" value="{{ $term->keperluan }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor
                                    ID</label>
                                <input type="text" value="{{ $term->vendorid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</label>
                                <input type="text" value="{{ $term->vendorname }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 3 (5 kolom) --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Terms
                                    ID</label>
                                <input type="text" value="{{ $term->terms_id }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">TOP ID</label>
                                <input type="text" value="{{ $term->topid }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">TOP
                                    Type</label>
                                <input type="text" value="{{ $term->top_type }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Terms
                                    Name</label>
                                <input type="text" value="{{ $term->terms_name }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                        </div>

                        {{-- Row 4 (5 kolom) --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Progress
                                    %</label>
                                <input type="text" value="{{ $term->progress_pct }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Payment
                                    %</label>
                                <input type="text" value="{{ $term->payment_pct }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Terms
                                    Type</label>
                                <input type="text" value="{{ $term->terms_type }}" readonly
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-gray-50 px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" />
                            </div>
                            <div class="flex flex-col gap-2 lg:col-span-2">
                                <label
                                    class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
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
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg></span>
                            <div>
                                <h2 class="text-base font-bold text-gray-800 dark:text-white">Photo Before</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Check the reference details, then fill in the quantities and attachments.</p>
                            </div>
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
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                        <div class="flex w-1/2 w-full flex-col border-b p-4">
                            <details class="group mb-4" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-sm font-semibold">Photo After</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <!-- file inputs tersembunyi untuk submit -->
                                    <div id="hiddenInputs"></div>
                                    <!-- picker hidden untuk open file dialog (multiple) -->
                                    <input type="file" id="hiddenPicker" class="hidden" accept="image/*"
                                        multiple>

                                    <!-- grid thumbnail -->
                                    <div id="attachmentsGrid"
                                        class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
                                        <!-- tile add photo -->
                                        <button type="button" id="addAttachmentTile"
                                            class="flex aspect-[4/3] items-center justify-center rounded-xl border-2 border-dashed border-gray-300 text-gray-500 hover:border-blue-500 hover:text-blue-600 dark:text-gray-400 dark:border-gray-700">
                                            <div class="flex flex-col items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-sm font-medium">Add Photo</span>
                                            </div>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Accepted: JPG/PNG, maks 5 MB per foto.</p>
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- Modal Lokasi -->
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
                        <div class="w-[95vw] max-w-2xl rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                                <button type="button" id="closeLokasi"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">×</button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Kiri: Location -->
                                <div>
                                    <label
                                        class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                    <select id="modal_location_id"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                                        <option value="">choose </option>
                                    </select>
                                </div>

                                <!-- Kanan: Sub Location (dependent) -->
                                <div>
                                    <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Location</label>
                                    <select id="modal_sub_location_id"
                                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
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

                    {{-- ===== Attachments ===== --}}
                    <div class="flex w-full flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <details class="group" open>
                            <summary class="flex cursor-pointer items-center justify-between border-b border-gray-100 pb-4 text-base font-bold text-gray-800 dark:border-gray-700 dark:text-white">
                                    <span class="flex items-center gap-3"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg></span><span>Attachments</span></span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden dark:text-gray-400">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline dark:text-gray-400">Hide
                                    details &darr;</span>
                            </summary>

                            <div class="flex flex-col pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments_ba[]"
                                            class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">
                                        <button type="button"
                                            class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60 hidden"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </div>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm font-medium text-gray-600 transition-colors hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Add Attachment
                            </button>
                        </details>

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <a href="{{ url()->previous() }}"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-red-500 px-5 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">Cancel</a>
                            <button type="submit" id="submitBtn"
                                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
        function showOverlay(text = 'Processing') {
            const $ov = $('#loadingSpinnerContainer');
            $ov.find('.loading-text').html((text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>');
            $ov.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    {{-- ===== Submit (tanpa validasi qty) ===== --}}
    <script>
        $(function() {
            function clearFormErrors() {
                $('#bastForm .is-invalid').removeClass('is-invalid').removeAttr('aria-invalid');
                $('#bastForm .error-feedback').remove();
            }

            function addError($el, msg) {
                if (!$el || !$el.length) return;
                $el.addClass('is-invalid').attr('aria-invalid', 'true');
                if ($el.next('.error-feedback').length === 0) {
                    $el.after('<small class="error-feedback">' + msg + '</small>');
                }
            }
            $(document).on('input change', '#bastForm input, #bastForm select', function() {
                $(this).removeClass('is-invalid').removeAttr('aria-invalid');
                $(this).next('.error-feedback').remove();
            });

            $('#addAttachment').on('click', function() {
                $('#attachmentsContainer').append(
                    '<div class="attachment-row flex items-center gap-2">' +
                    '<input type="file" name="attachments[]" class="flex-grow cursor-pointer rounded-lg border border-gray-300 bg-white py-0 pr-3 text-sm text-gray-600 shadow-sm transition file:mr-4 file:h-[42px] file:cursor-pointer file:border-0 file:bg-blue-50 file:px-4 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-gray-600 dark:file:text-gray-100">' +
                    '<button type="button" class="removeAttachment h-[42px] w-[42px] inline-flex shrink-0 items-center justify-center rounded-lg border border-red-100 bg-red-50 text-red-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:cursor-not-allowed disabled:opacity-60"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>' +
                    '</div>'
                );
            });
            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
            });

            $('#bastForm').on('submit', function(e) {
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
                    .done(function(res) {
                        if (window.toastr) toastr.success(res.message || 'Bast created successfully!');
                        window.location.href = "/bastlist";
                    })
                    .fail(function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let msg = 'Mohon periksa input:<br>';
                            Object.keys(xhr.responseJSON.errors).forEach(k => {
                                msg += `- ${xhr.responseJSON.errors[k].join(', ')}<br>`;
                            });
                            if (window.toastr) toastr.error(msg);
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            if (window.toastr) toastr.error(xhr.responseJSON.message);
                        } else {
                            if (window.toastr) toastr.error('Error! Please check the input.');
                        }
                    })
                    .always(function() {
                        $('#submitBtn').prop('disabled', false);
                        $('#btnText').text('Submit Approval');
                        hideOverlay();
                    });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script>
        $(function() {
            // ganti sesuai kebutuhanmu; sesuai snippet awalmu menggunakan $bq->bqid
            const listUrl = @json(route('attachments.list', ['doctype' => 'BQ', 'refnbr' => $term->bqid]));

            const $grid = $('#bqAttachmentGrid');

            function cardTpl(at) {
                const name = at.name || at.display_name || '(no name)';
                const by = at.created_user ?? at.created_by ?? '-';
                const dateStr = at.created_at ? dayjs(at.created_at).format("DD MMM 'YY") : '-';
                const ext = (at.extention || '').toLowerCase();
                const href = at.url || '#';
                const isImg = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext);

                const thumb = isImg && at.url ?
                    `<img src="${href}" alt="${name}" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy" referrerpolicy="no-referrer">` :
                    `<div class="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-gray-700">
                        <span class="text-lg">${ ext === 'pdf' ? '📕' : '📄' }</span>
                    </div>`;

                return `
                    <div class="group relative flex flex-col overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-gray-500 dark:border-gray-700 dark:bg-gray-800 min-w-[120px]">
                        <a ${at.url ? `href="${href}" target="_blank"` : ''} class="relative block aspect-square overflow-hidden">
                            ${thumb}
                            <div class="absolute inset-0 bg-black/0 transition group-hover:bg-black/20"></div>
                        </a>
                        <div class="px-4 py-3">
                            <div class="truncate  text-sm  font-medium text-gray-900 dark:text-gray-100" title="${name}">
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
                    toastr?.error?.('File bukan gambar.');
                    return;
                }
                if (file.size > MAX_SIZE) {
                    toastr?.error?.(`Ukuran melebihi 5MB: ${file.name}`);
                    return;
                }
                if (hiddenInputs.querySelectorAll('input[type="file"][name="attachments[]"]').length >= MAX_FILES) {
                    toastr?.error?.(`Maksimal ${MAX_FILES} foto.`);
                    return;
                }
                const key = `${file.name}::${file.size}`;
                if (chosenKeys.has(key)) {
                    toastr?.warning?.(`Lewati duplikat: ${file.name}`);
                    return;
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
        $(function() {

            // cpny langsung dari Blade, tidak pakai select lagi
            const cpny = "{{ $term->cpny_id }}";

            function openLokasiModal() {
                $('#modalLokasi').removeClass('hidden').addClass('flex');
            }

            function closeLokasiModal() {
                $('#modalLokasi').addClass('hidden').removeClass('flex');
            }

            // buka modal & load locations berdasarkan company
            $('#btnLokasi').on('click', function() {
                // reset
                $('#modal_location_id')
                    .empty()
                    .append('<option value="">choose </option>');

                $('#modal_sub_location_id')
                    .empty()
                    .append('<option value="">choose </option>');

                // load locations tanpa cek cpny karena sudah fix dari Blade
                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`, function(list) {
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
            $('#modal_location_id').on('change', function() {
                const loc = $(this).val();
                const $sub = $('#modal_sub_location_id');

                $sub.empty().append('<option value="">choose </option>');

                if (!loc) return;

                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`,
                    function(list) {
                        list.forEach(it =>
                            $sub.append(new Option(it.text, it.value))
                        );
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

        });
    </script>



    {{-- Toastr CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
