{{-- resources/views/pages/wos/editwos.blade.php --}}
<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                {{-- ====== EDIT FORM ====== --}}
                <form id="woForm" class="flex flex-col gap-4" enctype="multipart/form-data"
                    action="{{ route('wos.update', $prefill['hash']) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="w-full rounded-xl bg-white p-4 shadow dark:bg-gray-800">

                        <!-- Header -->
                        <div class="mb-6 flex items-center justify-between border-b pb-3 dark:border-gray-700">
                            <h2 class="text-base font-bold text-gray-800 dark:text-white">✏️ Edit WO —
                                {{ $prefill['woid'] }}</h2>
                        </div>

                        <!-- Row 1 -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-5">

                            <!-- Company -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select name="cpnyid" id="cpnyid" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ $p->cpny_id == $prefill['cpnyid'] ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- ✅ Business Unit -->
                            <div class="flex flex-col gap-2">
                                <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Business
                                    Unit</label>
                                <select name="business_unit_id" id="business_unit_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="" disabled selected>Loading...</option>
                                </select>
                            </div>

                            <!-- Department -->
                            <div class="flex flex-col gap-2">
                                <label
                                    class="req text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select name="departementid" id="departementid" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->department_id }}"
                                            {{ $p->department_id == $prefill['departementid'] ? 'selected' : '' }}>
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
                                    <option value="">-- choose --</option>
                                </select>
                            </div>

                            <!-- WO Request -->
                            <div class="flex flex-col gap-2">
                                <label class="req text-sm font-medium text-gray-700 dark:text-gray-300">WO
                                    Request</label>
                                <select name="worequest" id="worequest" required
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    <option value="">-- choose --</option>
                                </select>
                            </div>
                        </div>

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

                        <!-- Row 2 -->
                        {{-- <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">

                            <!-- Lokasi -->
                            <div class="flex flex-col gap-2">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">Lokasi</label>
                                <div class="flex gap-2">
                                    <input id="lokasi_display" readonly
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Location & Sub Location">
                                    <button type="button" id="btnLokasi"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button>
                                </div>
                                <input type="hidden" name="location_id" id="location_id">
                                <input type="hidden" name="sub_location_id" id="sub_location_id">
                            </div>

                            <!-- Jenis Pekerjaan -->
                            <div class="flex flex-col gap-2">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">Jenis
                                    Pekerjaan</label>
                                <div class="flex gap-2">
                                    <input id="jenis_pekerjaan_display" readonly
                                        class="flex-1 rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-gray-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Pilih Worktype & Subworktype">
                                    <button type="button" id="btnJenisPekerjaan"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Pilih</button>
                                </div>
                                <input type="hidden" name="worktypeid" id="worktypeid">
                                <input type="hidden" name="subworktypeid" id="subworktypeid">
                            </div>

                            <!-- PIC Requester -->
                            <div class="flex flex-col gap-2">
                                <label class="req  text-sm  font-medium text-gray-700 dark:text-gray-300">PIC
                                    Requester</label>
                                <input type="text" name="picrequester" id="picrequester"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                            </div>

                            <!-- Biaya WO -->
                            <div class="flex flex-col gap-2">
                                <label class=" text-sm  font-medium text-gray-700 dark:text-gray-300">Biaya WO</label>
                                <input type="number" step="0.01" min="0" name="biaya_wo" id="biaya_wo"
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    placeholder="0.00">
                            </div>
                        </div> --}}

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
                                    <option value="">-- choose --</option>
                                    <option value="Pemberi Kerja">Pemberi Kerja</option>
                                    <option value="Penerima Kerja">Penerima Kerja</option>
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
                                <button type="button" id="btnBudget"
                                    class="rounded border border-gray-500 px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-700"
                                    title="Lookup">
                                    🔎
                                </button>
                            </div>

                            <!-- hidden -->
                            <input type="hidden" name="activity_id" id="activity_id">
                            <input type="hidden" name="coa_business_unit_id" id="coa_business_unit_id">
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

                    {{-- ===== Modal Jenis Pekerjaan ===== --}}
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
                                <div>
                                    <label
                                        class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Worktype</label>
                                    <select id="modal_worktypeid"
                                        class="mt-1 w-full rounded-lg border p-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Worktype</label>
                                    <select id="modal_subworktypeid"
                                        class="mt-1 w-full rounded-lg border p-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
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

                    {{-- ===== Modal Lokasi ===== --}}
                    <div id="modalLokasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                        <div class="w-[95vw] max-w-2xl rounded-xl bg-white p-4 dark:bg-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Pilih Lokasi</h3>
                                <button type="button" id="closeLokasi"
                                    class="text-lg leading-none text-gray-400 hover:text-gray-600">×</button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label
                                        class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                                    <select id="modal_location_id"
                                        class="mt-1 w-full rounded-lg border p-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="req block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                        Location</label>
                                    <select id="modal_sub_location_id"
                                        class="mt-1 w-full rounded-lg border p-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">-- choose --</option>
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

                    {{-- ===== Attachments ===== --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span class="req">Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            {{-- Existing attachments (signed URL) --}}
                            <div id="attachmentsList" class="mt-6 flex flex-col gap-2">
                                @forelse ($attachments as $att)
                                    <div class="attachment-row flex items-center justify-between gap-3 rounded-lg border border-gray-200 p-3 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/40"
                                        data-id="{{ $att->id }}">
                                        <div class="flex min-w-0 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                📎</div>
                                            <div class="min-w-0">
                                                @if ($att->url)
                                                    <a href="{{ $att->url }}" target="_blank"
                                                        class="block truncate font-medium text-indigo-700 hover:underline dark:text-indigo-300">
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
                                            class="removeAttachment2 inline-flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700/40 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                                            aria-label="Remove attachment">
                                            🗑️
                                        </button>
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
                                        class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                    <button type="button"
                                        class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                            <button type="button" id="cancelBtn"
                                class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn"
                                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                $select.html('<option value="">-- choose --</option>');
                $.getJSON(`/wos/ajax/categories/${categoryid}`)
                    .done(function(list) {
                        list.forEach(it => $select.append(new Option(it.text, it.text)));
                        if (selectedText) {
                            selectByText($select, selectedText);
                        }
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat kategori ' + categoryid);
                    });
            }
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
                $('#modal_worktypeid').html('<option value="">-- choose --</option>');
                $('#modal_subworktypeid').html('<option value="">-- choose --</option>');
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
                $sub.html('<option value="">-- choose --</option>');
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

            function openLokasiModal() {
                $('#modalLokasi').removeClass('hidden').addClass('flex');
            }

            function closeLokasiModal() {
                $('#modalLokasi').addClass('hidden').removeClass('flex');
            }

            // buka modal & load locations by cpny
            $('#btnLokasi').on('click', function() {
                const cpny = $cpny.val();
                if (!cpny) {
                    toastr.error('Pilih Company terlebih dahulu.');
                    return;
                }
                $('#modal_location_id').html('<option value="">-- choose --</option>');
                $('#modal_sub_location_id').html('<option value="">-- choose --</option>');
                $.getJSON(`/wos/ajax/locations/${encodeURIComponent(cpny)}`)
                    .done(function(list) {
                        list.forEach(it => $('#modal_location_id').append(new Option(it.text, it
                            .value)));
                        openLokasiModal();
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Location.');
                    });
            });

            $('#closeLokasi, #cancelLokasi').on('click', closeLokasiModal);

            // load sub locations on change
            $('#modal_location_id').on('change', function() {
                const cpny = $cpny.val();
                const loc = $(this).val();
                const $sub = $('#modal_sub_location_id');
                $sub.html('<option value="">-- choose --</option>');
                if (!cpny || !loc) return;
                $.getJSON(`/wos/ajax/sublocations/${encodeURIComponent(cpny)}/${encodeURIComponent(loc)}`)
                    .done(function(list) {
                        list.forEach(it => $sub.append(new Option(it.text, it.value)));
                    })
                    .fail(function() {
                        toastr.error('Gagal memuat Sub Location.');
                    });
            });

            // save to hidden + display
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

            // clear if company changed
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
                        <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2  text-sm  text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file: text-sm  file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                        <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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
                    $tbody.html(`<tr><td colspan="4" class="border p-3 text-center text-gray-500">No data</td></tr>`);
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
                        <td class="border p-2">${esc(accLabel)}</td>
                        <td class="border p-2">${esc(actLabel)}</td>
                        <td class="border p-2">${esc(fmtNum(remaining))}</td>
                        <td class="border p-2 text-center">
                            <button type="button"
                                class="coaPick rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700"
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

                $tbody.html(`<tr><td colspan="4" class="border p-3 text-center text-gray-500">Loading...</td></tr>`);

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
                    $tbody.html(`<tr><td colspan="4" class="border p-3 text-center text-red-600">Failed load COA</td></tr>`);
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
                $coaTbody.html('<tr><td colspan="4" class="p-3 text-center">Loading...</td></tr>');
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
                            $coaTbody.html('<tr><td colspan="4" class="p-3 text-center">No data</td></tr>');
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
                            <td class="border p-2">${esc(accountId)}</td>
                            <td class="border p-2">${esc(actDescr)}</td>
                            <td class="border p-2">
                                <div class="font-semibold">${esc(remaining)}</div>
                                <div class="text-sm opacity-70">Available : ${esc(available)}</div>
                                <div class="text-sm opacity-70">Used : ${esc(used)}</div>
                            </td>
                            <td class="border p-2 text-center">
                                <button type="button"
                                    class="chooseCoa rounded border px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700"
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
                            '<tr><td colspan="4" class="p-3 text-center text-red-600">Failed to load</td></tr>'
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








    {{-- Toastr --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</x-app-layout>
