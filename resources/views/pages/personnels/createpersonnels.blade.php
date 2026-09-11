<x-app-layout>

    <div class="mx-auto w-full max-w-9xl p-2">

        <form id="personnelForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_draft" id="isDraftField" value="0">

            <div class="flex flex-col gap-6">

                    {{-- 1. POSITION --}}
                    <details id="sec-position" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">1</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Position</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Where this role sits in the organization</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="cpnyid" required>
                                        @foreach ($usercpny as $p)
                                            <option value="{{ $p->cpny_id }}"
                                                {{ $p->cpny_id == $usercpny2->cpny_id ? 'selected' : '' }}>
                                                {{ $p->cpny_id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division <span class="text-red-500">*</span></label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="division" id="division_id" required>
                                        <option value="" disabled selected>Select Division</option>
                                        @foreach ($userdivison as $p)
                                            <option value="{{ $p->division_id }}">{{ $p->division_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department <span class="text-red-500">*</span></label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="departementid" id="departementid" required>
                                        <option value="" disabled selected>Select Department</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Placement Location <span class="text-red-500">*</span></label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="siteid" id="siteid" required>
                                        <option value="">Select Site </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- 2. JOB DETAIL --}}
                    <details id="sec-jobdetail" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">2</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Job Detail</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Role, level and reporting line</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">

                            <div id="jobTypeRow" class="mb-6 grid grid-cols-1 gap-6">
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Type <span class="text-red-500">*</span></label>
                                    <select name="job_type" id="job_type"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        required>
                                        <option value="" disabled>Select Job Type</option>
                                        <option value="New">New</option>
                                        <option value="Replacement" selected>Replacement</option>
                                    </select>
                                </div>
                                <div id="replacementField" class="hidden flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Replacement Name</label>
                                    <input type="text" name="immediate_replacement" id="immediate_replacement"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        placeholder="Enter employee name to be replaced">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Job Title <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="job_title" id="job_title"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        required>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        * Hanya tuliskan nama job. Contoh: <b>Promotion</b> / <b>IT</b>. Tidak perlu level (Staff, Officer, dll).
                                    </span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Level <span class="text-red-500">*</span></label>
                                    <input type="hidden" name="group_grade" id="group_grade">
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="subgrade_id" id="subgrade_id" required>
                                        @foreach ($subgradings as $sg)
                                            <option value="{{ $sg->subgrade_id }}"
                                                data-group="{{ $sg->group_grade }}">
                                                {{ $sg->subgrade_id }} - {{ $sg->subgrade_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Immediate Superior</label>
                                    <select name="immediate_superior" id="immediate_superior"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <option value="">Select User</option>
                                        @foreach ($activeUsers as $u)
                                            <option value="{{ $u->username }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Superior Position</label>
                                    <input type="text" name="state_position" id="state_position"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                </div>
                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Vacancy <span class="text-red-500">*</span></label>
                                    <textarea name="reason_vacancy" id="reason_vacancy"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        required></textarea>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-4 border-t border-dashed border-gray-200 pt-6 dark:border-gray-700 sm:grid-cols-3">
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-gray-200 bg-gray-50 p-3.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actual <span class="text-red-500">*</span></label>
                                    <input type="number" name="actual" id="actual" min="0"
                                        class="number-only w-full rounded-md border border-gray-300 bg-white p-2 text-base font-bold text-gray-800 focus:ring focus:ring-indigo-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        required>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Current headcount</span>
                                </div>
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-gray-200 bg-gray-50 p-3.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Number Required <span class="text-red-500">*</span></label>
                                    <input type="number" name="required" id="required" min="0"
                                        class="number-only w-full rounded-md border border-gray-300 bg-white p-2 text-base font-bold text-gray-800 focus:ring focus:ring-indigo-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        required>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Positions being requested</span>
                                </div>
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 p-3.5 dark:border-indigo-800 dark:bg-indigo-900/20">
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                                        <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h5M8 17h3"/></svg>
                                        Total Actual Number <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="total_actual" id="total_actual" min="0"
                                        class="number-only w-full rounded-md border border-indigo-200 bg-white p-2 text-base font-bold text-indigo-700 focus:ring focus:ring-indigo-300 dark:border-indigo-800 dark:bg-gray-800 dark:text-indigo-300"
                                        required>
                                    <span class="flex items-center gap-1 text-[11px] text-indigo-600/80 dark:text-indigo-400/80">
                                        <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="9" rx="1.5"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                                        Auto-calculated
                                    </span>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- 3. JOB RESPONSIBILITIES --}}
                    <details id="sec-responsibilities" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">3</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Job Responsibilities <span class="text-red-500">*</span></div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">List the key duties of this role</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">
                            <div id="responsibilitiesTable" class="flex flex-col gap-2">
                                <div class="responsibilities-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                    </span>
                                    <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">1</span>
                                    <input type="text" name="responsibilities[]" required
                                        placeholder="Type here..."
                                        class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                                    <button type="button"
                                        class="removeResponsibilities hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" id="addResponsibilities"
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Add Responsibility
                            </button>
                        </div>
                    </details>

                    {{-- 4. JOB QUALIFICATION --}}
                    <details id="sec-qualifications" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">4</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Job Qualification</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Education, experience and skills required</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">

                            <div class="mb-2 flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span> Education &amp; Experience <span class="text-red-500">*</span>
                            </div>
                            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Education <span class="text-red-500">*</span></label>
                                    <select name="education" id="education" required
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        <option value="" disabled selected>Select</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA / SMK">SMA / SMK</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Experience — Start <span class="text-red-500">*</span></label>
                                    <input type="number" name="experience_start" id="experience_start" required
                                        min="0" placeholder="0"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Experience — End <span class="text-red-500">*</span></label>
                                    <input type="number" name="experience_end" id="experience_end" required
                                        min="0" placeholder="0"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                </div>
                            </div>

                            <div class="mb-2 flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span> Skills <span class="text-red-500">*</span>
                            </div>
                            <div id="qualificationTable" class="flex flex-col gap-2">
                                <div class="qualification-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                    </span>
                                    <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">1</span>
                                    <input type="text" name="qualification[]" required
                                        placeholder="Type here..."
                                        class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                                    <button type="button"
                                        class="removeQualification hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                    </button>
                                </div>
                            </div>
                            <button type="button" id="addQualification"
                                class="mb-6 mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg> Add Skill
                            </button>

                            <div class="flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span> Tags
                            </div>
                            <div class="mt-2 w-full min-w-0">
                                <select name="tags[]" id="tags" multiple
                                    class="tags-input block w-full min-w-0 rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                </select>
                            </div>
                        </div>
                    </details>

                    {{-- 5. ATTACHMENTS --}}
                    <details id="sec-attachments" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">5</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Attachments</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Supporting documents (optional)</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">
                            <div class="flex max-h-[220px] flex-col gap-2 overflow-y-auto">
                                <div id="attachmentsContainer" class="flex flex-col gap-2">
                                    <div class="attachment-row flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-900/30">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-600 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg> Add Attachment
                            </button>
                        </div>
                    </details>

                    {{-- FOOTER ACTIONS --}}
                    <div class="sticky bottom-2 z-10 flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-white/95 p-4 shadow-sm backdrop-blur dark:border-gray-700 dark:bg-gray-800/95">
                        <button id="backBtn" type="button" onclick="history.back()"
                            class="flex items-center gap-2 rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Back</span>
                        </button>
                        <button type="submit" id="submitBtn"
                            class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                            <span id="btnText">Submit Approval</span>
                            <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                            </svg>
                        </button>
                    </div>

            </div>
        </form>

        <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
            Personnel Requisition Created Successfully!
        </div>
    </div>

    <div id="loadingSpinnerContainer" role="status" aria-live="polite" aria-label="Loading">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text">
                Processing<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>
            </div>
        </div>
    </div>

    <script>
        function showOverlay(text = 'Processing') {
            const $overlay = $('#loadingSpinnerContainer');
            $overlay.find('.loading-text').html(
                (text || 'Processing') +
                '<span class="loading-ellipsis"><span>.</span><span>.</span><span>.</span></span>'
            );
            $overlay.stop(true, true).fadeIn(120);
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').stop(true, true).fadeOut(120);
        }
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                showOverlay('Submitting');

                $.ajax({
                    url: "{{ route('personnels.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#personnelForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Personnel Requisition Submit Successfully!");
                        window.location.href = "/personnels";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
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
                    window.location.href = "{{ route('personnels') }}";
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-900/30">
                        <input type="file" name="attachments[]" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                        <button type="button" class="removeAttachment flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
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
        $(document).ready(function() {
            let responsibilityCount = 1;

            // Fungsi untuk Menambah Baris Responsibility
            $('#addResponsibilities').click(function() {
                responsibilityCount++;
                $('#responsibilitiesTable').append(`
                    <div class="responsibilities-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                        </span>
                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">${responsibilityCount}</span>
                        <input type="text" name="responsibilities[]" required placeholder="Type here..." class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                        <button type="button" class="removeResponsibilities hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
                    </div>
                `);
                updateRemoveButtons();
            });

            // Fungsi untuk Menghapus Baris Responsibility
            $(document).on('click', '.removeResponsibilities', function() {
                $(this).closest('.responsibilities-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
            });

            // Fungsi untuk Memperbarui Nomor pada Tabel
            function updateRowNumbers() {
                responsibilityCount = 0;
                $('#responsibilitiesTable .responsibilities-row').each(function() {
                    responsibilityCount++;
                    $(this).find('.row-num').text(responsibilityCount);
                });
            }

            // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
            function updateRemoveButtons() {
                if ($('.responsibilities-row').length > 1) {
                    $('.removeResponsibilities').removeClass('hidden');
                } else {
                    $('.removeResponsibilities').addClass('hidden');
                }
            }

            updateRemoveButtons();

        });
    </script>

    <script>
        $(document).ready(function() {
            let qualificationCount = 1;

            // Fungsi untuk Menambah Baris Qualification
            $('#addQualification').click(function() {
                qualificationCount++;
                $('#qualificationTable').append(`
                    <div class="qualification-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                        </span>
                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">${qualificationCount}</span>
                        <input type="text" name="qualification[]" required placeholder="Type here..." class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                        <button type="button" class="removeQualification hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
                    </div>
                `);
                updateRemoveButtons();
            });

            // Fungsi untuk Menghapus Baris Qualification
            $(document).on('click', '.removeQualification', function() {
                $(this).closest('.qualification-row').remove();
                updateRowNumbers();
                updateRemoveButtons();
            });

            // Fungsi untuk Memperbarui Nomor pada Tabel
            function updateRowNumbers() {
                qualificationCount = 0;
                $('#qualificationTable .qualification-row').each(function() {
                    qualificationCount++;
                    $(this).find('.row-num').text(qualificationCount);
                });
            }

            // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
            function updateRemoveButtons() {
                if ($('.qualification-row').length > 1) {
                    $('.removeQualification').removeClass('hidden');
                } else {
                    $('.removeQualification').addClass('hidden');
                }
            }

            updateRemoveButtons();
        });
    </script>
    <script>
        $(document).ready(function() {
            // Cegah input selain angka saat mengetik
            $('.number-only').on('keypress', function(event) {
                let charCode = event.which ? event.which : event.keyCode;
                if (charCode < 48 || charCode > 57) {
                    event.preventDefault();
                }
            });

            // Hapus karakter selain angka jika sudah terlanjur masuk
            $('.number-only').on('input', function() {
                let value = $(this).val();
                $(this).val(value.replace(/[^0-9]/g, ''));
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            // Bikin total_actual jadi readonly
            $('#total_actual').prop('readonly', true);

            // Kalau Actual atau Required berubah
            $('#actual, #required').on('input', function() {
                let actual = parseInt($('#actual').val()) || 0;
                let required = parseInt($('#required').val()) || 0;
                let total = actual + required;

                // Set hasil ke total_actual
                $('#total_actual').val(total);
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            let tagsData = @json($skillTags);

            // Format data agar bisa dibaca select2
            let formattedTags = tagsData.map(tag => {
                return {
                    id: tag.job_tags,
                    text: tag.job_tags
                };
            });

            $('#tags').select2({
                data: formattedTags,
                placeholder: "Select or type tags",
                tags: true, // agar bisa ketik bebas juga
                tokenSeparators: [',']
            });
        });
    </script>
    <script>
        // Kalau select2 cuma punya 1 pilihan (selain placeholder), langsung auto select
        function autoSelectIfSingleOption($select) {
            const options = $select.find('option').filter(function() {
                return $(this).val() !== '' && !$(this).prop('disabled');
            });
            if (options.length === 1) {
                $select.val(options.eq(0).val()).trigger('change');
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            // Fungsi ketika Company berubah
            $('select[name="cpnyid"]').on('change', function() {
                var cpnyid = $(this).val();

                if (cpnyid) {
                    $.ajax({
                        url: `/api/sites/${cpnyid}`,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            let $siteSelect = $('select[name="siteid"]');
                            $siteSelect.empty();
                            $siteSelect.append('<option value="">Select Site </option>');

                            $.each(data, function(key, value) {
                                $siteSelect.append(
                                    `<option value="${value.site}">${value.site}</option>`
                                );
                            });

                            autoSelectIfSingleOption($siteSelect);
                        }
                    });
                } else {
                    $('select[name="siteid"]').empty().append(
                        '<option value="">Select Site </option>');
                }
            });

            // 🔄 Trigger langsung saat load untuk mengisi default site
            $('select[name="cpnyid"]').trigger('change');
        });
    </script>
    <script>
        $(document).ready(function() {


            function loadJobTitles() {
                let deptId = $('select[name="departementid"]').val();
                let jobType = $('#job_type').val();
                let $jobTitle = $('#job_title');

                $jobTitle.empty().append('<option value="">Loading...</option>');

                if (!deptId || !jobType) {
                    $jobTitle.html('<option value="">Select</option>');
                    return;
                }

                let url =
                    jobType === 'New' ?
                    `/api/vacant-employees/${deptId}` // Untuk VACANT (default)
                    :
                    `/api/replacement-employees/${deptId}`; // Untuk pengganti (non-VACANT)

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $jobTitle.empty().append(
                            '<option value="">Select</option>');

                        if (data.length > 0) {
                            $.each(data, function(key, emp) {
                                const subgradeId = emp.subgrade_id ?? '';

                                $jobTitle.append(`
                                    <option value="${emp.departement_id}"
                                            data-title-level="${emp.subgrade_name}"
                                            data-parent-id="${emp.parent_id}"
                                            data-subgrade-id="${subgradeId}">
                                        ${emp.departement_name}-${emp.subgrade_name}
                                    </option>`);
                            });
                        } else {
                            $jobTitle.append('<option value="">No positions found</option>');
                        }
                    },
                    error: function() {
                        $jobTitle.html('<option value="">Error loading data</option>');
                    }
                });
            }

            // Jalankan saat departementid atau job_type berubah
            // $('select[name="departementid"], #job_type').on('change', function() {
            //     loadJobTitles();
            // });


            $('#job_title').on('change', function() {
                let selected = $(this).find(':selected');
                let titleLevel = selected.data('title-level') || '';
                let parentId = selected.data('parent-id') || '';
                let deptId = $('select[name="departementid"]').val();

                $('#job_level').val(titleLevel).prop('readonly', true); // isi title level

                // SET subgrade_id
                const subgradeId = selected.data('subgrade-id') || '';
                $('#subgrade_id').val(subgradeId).trigger('change');

                if (parentId) {
                    $.ajax({
                        url: `/api/job-parent-info/${parentId}/${selected.val()}/${deptId}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            // Isi experience dan education
                            $('#experience_start').val(data.experience_min || '').prop(
                                'readonly', true);
                            $('#experience_position').val(data.experience_position || '').prop(
                                'readonly', true);
                            $('#education_min').val(data.education_min || '').prop('readonly',
                                true);
                            $('#education_jurusan').val(data.education_jurusan || '').prop(
                                'readonly', true);
                            $('#actual').val(data.actual).prop('readonly', true);
                            $('#required').val(data.required).prop('readonly', true);
                            $('#total_actual').val(data.total_actual).prop('readonly', true);


                            // Tampilkan job profile ke tabel
                            let $tbody = $('#jobProfileTable tbody');
                            $tbody.empty();

                            if (data.job_profile && data.job_profile.length > 0) {
                                $.each(data.job_profile, function(index, row) {
                                    $tbody.append(`
                                        <tr>
                                            <td class="border p-2 text-center">${row.no_job_purpose}</td>
                                            <td class="border p-2">${row.job_purpose}</td>
                                            <input type="hidden" name="responsibilities[]" value="${row.job_purpose}">
                                        </tr>
                                    `);
                                });
                            } else {
                                $tbody.append(
                                    '<tr><td colspan="2" class="text-center p-2 border">No job profile found</td></tr>'
                                );
                            }
                        },
                        error: function() {
                            $('#immediate_superior').val('').trigger('change');
                            $('#state_position').val('');
                            $('#experience_start').val('');
                            $('#experience_position').val('');
                            $('#education_min').val('');
                            $('#education_jurusan').val('');
                            $('#jobProfileTable tbody').html(
                                '<tr><td colspan="2" class="text-center p-2 border">Error loading job profile</td></tr>'
                            );
                        }
                    });
                }

            });


            // 🔄 Trigger saat awal untuk load data berdasarkan departemen terpilih
            $('select[name="departementid"]').trigger('change');
        });
    </script>
    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $dept = $('select[name="departementid"]');

            $cpny.select2({
                placeholder: 'Select Company',
                width: '100%',
                allowClear: true
            });
            $dept.select2({
                placeholder: 'Select Department',
                width: '100%',
                allowClear: true
            });
            $('#subgrade_id').select2({
                placeholder: 'Select Job Level',
                width: '100%',
                allowClear: false
            });
            $('#immediate_superior').select2({
                placeholder: 'Select User',
                width: '100%',
                allowClear: true
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subgradeSelect = document.getElementById("subgrade_id");
            const hiddenGroupInput = document.getElementById("group_grade");

            function updateGroupGrade() {
                const selected = subgradeSelect.options[subgradeSelect.selectedIndex];
                hiddenGroupInput.value = selected.dataset.group ?? "";
            }

            // trigger saat pertama kali load
            updateGroupGrade();

            // trigger saat pilihan berubah
            subgradeSelect.addEventListener("change", updateGroupGrade);
        });

        $(document).ready(function () {

            function toggleReplacementField() {
                let jobType = $('#job_type').val();

                if (jobType === 'Replacement') {
                    $('#replacementField')
                        .removeClass('hidden')
                        .addClass('flex');
                    $('#jobTypeRow').addClass('md:grid-cols-2');

                    $('#immediate_replacement').attr('required', true);

                } else {
                    $('#replacementField')
                        .addClass('hidden')
                        .removeClass('flex');
                    $('#jobTypeRow').removeClass('md:grid-cols-2');

                    $('#immediate_replacement').val('');
                    $('#immediate_replacement').removeAttr('required');
                }
            }

            // 👉 trigger saat change
            $('#job_type').on('change', toggleReplacementField);

            // 👉 trigger saat pertama load
            toggleReplacementField();

        });

        $(document).on('keydown', 'input, textarea', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                return false;
            }
        });

    </script>

    <script>
        $(function() {
            const $division = $('#division_id');
            const $dept = $('#departementid');

            // select2 init
            $division.select2({
                placeholder: 'Select Division',
                width: '100%',
                allowClear: true
            });
            $dept.select2({
                placeholder: 'Select Department',
                width: '100%',
                allowClear: true
            });

            // Auto select division kalau cuma ada 1 pilihan
            autoSelectIfSingleOption($division);

            function resetDept(message = 'Select Department') {
                $dept.empty().append(`<option value="" disabled selected>${message}</option>`);
                $dept.val(null).trigger('change'); // reset select2 value
            }

            function loadDepartments(divisionId) {
                resetDept('Loading...');

                $.ajax({
                    url: `/hr/departments`,
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        division_id: divisionId
                    },
                    success: function(rows) {
                        resetDept('Select Department');

                        if (rows && rows.length) {
                            rows.forEach(r => {
                                // NOTE: value bisa kamu pilih mau department_id atau department_name
                                // rekomendasi: pakai department_id (lebih aman buat relasi)
                                $dept.append(
                                    `<option value="${r.department_id}">${r.department_name}</option>`
                                );
                            });
                            autoSelectIfSingleOption($dept);
                        } else {
                            resetDept('No department found');
                        }
                    },
                    error: function() {
                        resetDept('Error loading department');
                    }
                });
            }

            // on division change
            $division.on('change', function() {
                const divisionId = $(this).val();
                if (!divisionId) {
                    resetDept();
                    return;
                }

                loadDepartments(divisionId);

                // OPTIONAL: kalau department berubah, job title kamu load berdasarkan dept -> tetap jalan
                // karena event departementid sudah ada di script kamu: $('select[name="departementid"], #job_type').on('change', ...)
            });

            // optional: kalau ada default division terpilih (edit mode), auto load dept
            if ($division.val()) {
                loadDepartments($division.val());
            } else {
                resetDept();
            }
        });
    </script>

</x-app-layout>
