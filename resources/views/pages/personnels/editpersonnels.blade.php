<x-app-layout>

    @php
        $statusMap = [
            'H' => ['label' => 'Draft', 'classes' => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-300/10 dark:text-slate-300 dark:border-slate-500/40'],
            'P' => ['label' => 'On Progress', 'classes' => 'bg-orange-100 text-orange-700 border-orange-300 dark:bg-orange-300/10 dark:text-orange-300 dark:border-orange-500/40'],
            'D' => ['label' => 'Revise', 'classes' => 'bg-amber-100 text-amber-700 border-amber-300 dark:bg-amber-300/10 dark:text-amber-300 dark:border-amber-500/40'],
            'C' => ['label' => 'Completed', 'classes' => 'bg-green-100 text-green-700 border-green-300 dark:bg-green-300/10 dark:text-green-300 dark:border-green-500/40'],
            'R' => ['label' => 'Rejected', 'classes' => 'bg-red-100 text-red-700 border-red-300 dark:bg-red-300/10 dark:text-red-300 dark:border-red-500/40'],
            'X' => ['label' => 'Cancel', 'classes' => 'bg-red-100 text-red-700 border-red-300 dark:bg-red-300/10 dark:text-red-300 dark:border-red-500/40'],
        ];
        $statusInfo = $statusMap[$personnel->status] ?? ['label' => $personnel->status ?? '-', 'classes' => 'bg-gray-100 text-gray-700 border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600'];
    @endphp

    <div class="mx-auto w-full max-w-9xl p-2">

        <form id="personnelForm" enctype="multipart/form-data" method="POST">
            @csrf
            <input type="hidden" name="is_draft" id="isDraftField" value="{{ old('is_draft', '0') }}">

            <div class="mb-5">
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs font-bold {{ $statusInfo['classes'] }}">
                            {{ $statusInfo['label'] }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            Doc ID {{ $personnel->docid }}
                            @if ($personnel->created_at)
                                &middot; Submitted {{ \Illuminate\Support\Carbon::parse($personnel->created_at)->format('M d, Y') }}
                            @endif
                        </span>
                    </div>

                    @if ($personnel->status === 'P' && $currentApproval)
                        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                            <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.2"/><path d="M5 20c1.2-3.6 4-5.5 7-5.5s5.8 1.9 7 5.5"/></svg>
                            Awaiting approval from <b class="font-semibold text-gray-700 dark:text-gray-200">{{ $currentApproval->aprv_name }}</b>
                        </div>
                    @endif
                </div>

                @if (in_array($personnel->status, ['R', 'D'], true) && $rejectionMessage)
                    <div class="mt-3 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-red-600 dark:bg-gray-800 dark:text-red-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l9 16H3z"/><path stroke-linecap="round" d="M12 10v4"/><circle cx="12" cy="17" r=".6" fill="currentColor" stroke="none"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-red-700 dark:text-red-300">
                                {{ $personnel->status === 'D' ? 'Revision Requested' : 'Rejection Reason' }}
                            </div>
                            <div class="mt-1 text-sm text-red-700/90 dark:text-red-200/90">{{ $rejectionMessage->message }}</div>
                            <div class="mt-1.5 text-[11px] text-red-600/70 dark:text-red-300/70">
                                {{ $rejectionMessage->name ?? $rejectionMessage->username }}
                                @if ($rejectionMessage->message_date)
                                    &middot; {{ \Illuminate\Support\Carbon::parse($rejectionMessage->message_date)->format('M d, Y') }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

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
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 {{ !empty($isSby) ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }}">
                                {{-- Company --}}
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="cpnyid" id="cpnyid" required>
                                        @foreach (!empty($isSby) ? $companies : $usercpny as $p)
                                            <option value="{{ $p->cpny_id }}"
                                                {{ (string) $p->cpny_id === (string) $personnel->cpnyid ? 'selected' : '' }}>
                                                {{ $p->cpny_id }}{{ !empty($isSby) && $p->cpny_name ? ' (' . $p->cpny_name . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if (!empty($isSby))
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget Company</label>
                                        <select
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            name="budget_entity_id" id="budget_entity_id" required>
                                            <option value="">Select Budget Company</option>
                                        </select>
                                    </div>
                                @endif

                                {{-- Division --}}
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="division_id" id="division_id" required>
                                        <option value="" disabled>Select Division</option>
                                        @foreach ($division as $p)
                                            <option value="{{ $p->division_id }}"
                                                {{ (string) old('division_id', $personnel->division_id ?? '') === (string) $p->division_id ? 'selected' : '' }}>
                                                {{ $p->division_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Department (AJAX loaded like create) --}}
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="departementid" id="departementid" required>
                                        <option value="" disabled {{ old('departementid', $personnel->departementid ?? '') ? '' : 'selected' }}>
                                            Select Department
                                        </option>
                                        @foreach ($departments as $dept)
                                            <option value="{{ $dept->department_id }}"
                                                {{ (string) old('departementid', $personnel->departementid ?? '') === (string) $dept->department_id ? 'selected' : '' }}>
                                                {{ $dept->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Site (AJAX loaded like create) --}}
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Placement Location</label>
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
                            <div class="flex shrink-0 items-center gap-3">
                                <div id="jobTypeSegmented" class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-gray-700">
                                    <button type="button" data-value="New"
                                        class="seg-btn rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors dark:text-gray-300">New</button>
                                    <button type="button" data-value="Replacement"
                                        class="seg-btn rounded-md px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors dark:text-gray-300">Replacement</button>
                                </div>
                                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                            </div>
                        </summary>
                        <div class="p-4 pt-3">

                            <select name="job_type" id="job_type" class="hidden" required>
                                <option value="" disabled>Select Job Type</option>
                                <option value="New" @selected(old('job_type', $personnel->job_type) === 'New')>New</option>
                                <option value="Replacement" @selected(old('job_type', $personnel->job_type) === 'Replacement')>Replacement</option>
                            </select>

                            <div id="replacementField" class="hidden mb-5 w-full flex-col gap-2 rounded-lg border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800 dark:bg-indigo-900/20">
                                <label class="block text-sm font-semibold text-indigo-800 dark:text-indigo-200">
                                    Replacement Name
                                </label>
                                <input type="text" name="immediate_replacement" id="immediate_replacement"
                                    value="{{ old('immediate_replacement', $personnel->immediate_replacement ?? '') }}"
                                    class="w-full rounded-lg border border-indigo-200 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-indigo-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Enter employee name to be replaced">
                                <span class="flex items-center gap-1.5 text-xs text-indigo-700/80 dark:text-indigo-300/80">
                                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8h.01M11 12h1v5h1"/></svg>
                                    Shown only when Job Type is set to Replacement.
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Title</label>
                                    <input type="text" name="job_title" id="job_title"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        value="{{ old('job_title', $personnel->job_title) }}" required>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job Level</label>
                                    <input type="hidden" name="group_grade" id="group_grade"
                                        value="{{ old('group_grade', $personnel->group_grade ?? '') }}">
                                    <select
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        name="subgrade_id" id="subgrade_id" required>
                                        @foreach ($subgradings as $sg)
                                            <option value="{{ $sg->subgrade_id }}"
                                                data-group="{{ $sg->group_grade ?? '' }}"
                                                @selected((string) $sg->subgrade_id === (string) old('subgrade_id', $personnel->subgrade_id))>
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
                                            <option value="{{ $u->username }}"
                                                @selected((string) $u->username === (string) old('immediate_superior', $personnel->immediate_superior))>
                                                {{ $u->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Superior Position</label>
                                    <input type="text" name="state_position" id="state_position"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        value="{{ old('state_position', $personnel->state_position) }}">
                                </div>

                                <div class="flex flex-col gap-2 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason for Vacancy</label>
                                    <textarea name="reason_vacancy" id="reason_vacancy"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        required>{{ old('reason_vacancy', $personnel->reason_vacancy) }}</textarea>
                                </div>
                            </div>

                            <div class="mt-6 grid grid-cols-1 gap-4 border-t border-dashed border-gray-200 pt-6 dark:border-gray-700 sm:grid-cols-3">
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-gray-200 bg-gray-50 p-3.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actual</label>
                                    <input type="number" name="actual" id="actual" min="0"
                                        class="number-only w-full rounded-md border border-gray-300 bg-white p-2 text-base font-bold text-gray-800 focus:ring focus:ring-indigo-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        value="{{ old('actual', $personnel->actual) }}">
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Current headcount</span>
                                </div>
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-gray-200 bg-gray-50 p-3.5 dark:border-gray-700 dark:bg-gray-900/30">
                                    <label class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Number Required</label>
                                    <input type="number" name="required" id="required" min="0"
                                        class="number-only w-full rounded-md border border-gray-300 bg-white p-2 text-base font-bold text-gray-800 focus:ring focus:ring-indigo-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                        value="{{ old('required', $personnel->required) }}">
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">Positions being requested</span>
                                </div>
                                <div class="flex min-w-0 flex-col gap-1.5 rounded-lg border border-indigo-200 bg-indigo-50 p-3.5 dark:border-indigo-800 dark:bg-indigo-900/20">
                                    <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-300">
                                        <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h5M8 17h3"/></svg>
                                        Total Actual Number
                                    </label>
                                    <input type="number" name="total_actual" id="total_actual" min="0"
                                        class="number-only w-full rounded-md border border-indigo-200 bg-white p-2 text-base font-bold text-indigo-700 focus:ring focus:ring-indigo-300 dark:border-indigo-800 dark:bg-gray-800 dark:text-indigo-300"
                                        value="{{ old('total_actual', $personnel->total_actual) }}" readonly>
                                    <span class="flex items-center gap-1 text-[11px] text-indigo-600/80 dark:text-indigo-400/80">
                                        <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="10" width="14" height="9" rx="1.5"/><path d="M8 10V7a4 4 0 018 0v3"/></svg>
                                        Auto-calculated
                                    </span>
                                </div>
                            </div>
                        </div>
                    </details>

                    {{-- 3. RESPONSIBILITIES --}}
                    <details id="sec-responsibilities" open class="group scroll-mt-4 rounded-xl bg-white shadow-sm dark:bg-gray-800">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 border-b border-gray-100 p-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-xs font-extrabold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">3</span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-white">Job Responsibilities</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">List the key duties of this role</div>
                                </div>
                            </div>
                            <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="p-4 pt-3">
                            <div id="responsibilitiesTable" class="flex flex-col gap-2">
                                @php $res = collect($jobres ?? []); @endphp
                                @if ($res->isEmpty())
                                    <div class="responsibilities-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                        </span>
                                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">1</span>
                                        <input type="text" name="responsibilities[]" placeholder="Type here..."
                                            class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                                        <button type="button"
                                            class="removeResponsibilities hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                        </button>
                                    </div>
                                @else
                                    @foreach ($res as $i => $resp)
                                        <div class="responsibilities-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                            <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                            </span>
                                            <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $i + 1 }}</span>
                                            <input type="text" name="responsibilities[]" placeholder="Type here..."
                                                class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200"
                                                value="{{ $resp->job_responsibilities_descr }}">
                                            <button type="button"
                                                class="removeResponsibilities hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
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
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span> Education &amp; Experience
                            </div>
                            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Education</label>
                                    <select name="education" id="education"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        <option value="" disabled
                                            {{ old('education', $personnel->education) ? '' : 'selected' }}>Select
                                        </option>
                                        @foreach (['SMP', 'SMA / SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $edu)
                                            <option value="{{ $edu }}" @selected(old('education', $personnel->education) === $edu)>
                                                {{ $edu }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Experience — Start</label>
                                    <input type="number" name="experience_start" id="experience_start"
                                        min="0"
                                        value="{{ old('experience_start', $personnel->experience_start) }}"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Experience — End</label>
                                    <input type="number" name="experience_end" id="experience_end"
                                        min="0"
                                        value="{{ old('experience_end', $personnel->experience_end) }}"
                                        placeholder="0"
                                        class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                </div>
                            </div>

                            <div class="mb-2 flex items-center gap-2 text-xs font-bold text-gray-800 dark:text-gray-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-indigo-600"></span> Skills
                            </div>
                            <div id="qualificationTable" class="flex flex-col gap-2">
                                @php $qua = collect($jobqua ?? []); @endphp
                                @if ($qua->isEmpty())
                                    <div class="qualification-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                        </span>
                                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">1</span>
                                        <input type="text" name="qualification[]" placeholder="Type here..."
                                            class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                                        <button type="button"
                                            class="removeQualification hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                        </button>
                                    </div>
                                @else
                                    @foreach ($qua as $i => $q)
                                        <div class="qualification-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                                            <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                                            </span>
                                            <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $i + 1 }}</span>
                                            <input type="text" name="qualification[]" placeholder="Type here..."
                                                class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200"
                                                value="{{ $q->job_qualification_descr }}">
                                            <button type="button"
                                                class="removeQualification hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach
                                @endif
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
                                @php
                                    $selected = collect(old('tags', $selectedTags ?? []))
                                        ->filter(fn($t) => filled($t))
                                        ->map(fn($t) => (string) trim($t))
                                        ->unique()
                                        ->values()
                                        ->toArray();

                                    $master = collect($skillTags ?? [])
                                        ->pluck('job_tags')
                                        ->filter(fn($t) => filled($t))
                                        ->map(fn($t) => (string) trim($t));

                                    $allTags = $master->merge($selected)->unique()->sort()->values();
                                @endphp

                                <select name="tags[]" id="tags" multiple
                                    class="tags-input block w-full min-w-0 rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:ring-2 focus:ring-indigo-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                    @foreach ($allTags as $tag)
                                        <option value="{{ $tag }}"
                                            {{ in_array($tag, $selected, true) ? 'selected' : '' }}>
                                            {{ $tag }}
                                        </option>
                                    @endforeach
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
                            <div class="flex max-h-[260px] flex-col gap-2 overflow-y-auto">
                                <div id="attachmentsContainer" class="flex flex-col gap-2">
                                    @foreach ($attachment as $attach)
                                        @php $fileUrl = route('attachments.view', ['id' => $attach->id]); @endphp
                                        <div class="attachment-row flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-900/30"
                                            data-attachid="{{ $attach->id }}">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/></svg>
                                            </div>
                                            <a href="{{ $fileUrl }}" target="_blank"
                                                class="flex-1 truncate text-sm text-gray-700 hover:text-indigo-600 hover:underline dark:text-gray-200">
                                                {{ $attach->attachment_name ?? basename($attach->filename) }}
                                            </a>
                                            <button type="button"
                                                class="removeAttachment2 flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                data-id="{{ $attach->id }}">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                                            </button>
                                        </div>
                                    @endforeach

                                    {{-- baris upload baru minimal 1 --}}
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

                        <div class="flex flex-col gap-3 md:flex-row md:items-center">
                            <button type="button" id="cancelBtn"
                                class="flex items-center gap-2 rounded-md border border-red-300 bg-white px-4 py-2 text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-500/40 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-500/10">
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-red-600"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
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

            </div>
        </form>

        <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
            Personnel Requisition Updated Successfully!
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

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        $(document).ready(function() {
            // ========= SUBMIT (AJAX PUT) =========
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let personnelHash = @json($hash);
                let updateUrl = `/personnels/${personnelHash}`;

                $('#submitBtn').attr('disabled', true);
                $('#btnText').text('Processing...');
                showOverlay('Updating');

                $.ajax({
                    url: updateUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-HTTP-Method-Override': 'PUT'
                    },
                    success: function(response) {
                        $('#successMessage').removeClass('hidden');
                        toastr.success("Personnel Requisition Updated Successfully!");
                        window.location.href = "/personnels";
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            alert('Error! Please check the input.');
                        }
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                        hideOverlay();
                    }
                });
            });

            $('#cancelBtn').click(function() {
                const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
                if (confirmed) window.location.href = "{{ route('personnels') }}";
            });

            // ========= SELECT2 INIT =========
            $('#cpnyid').select2({
                placeholder: 'Select Company',
                width: '100%',
                allowClear: true
            });
            $('#division_id').select2({
                placeholder: 'Select Division',
                width: '100%',
                allowClear: true
            });
            $('#departementid').select2({
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
            $('#tags').select2({
                placeholder: "Select or type tags",
                tags: true,
                tokenSeparators: [','],
                width: '100%'
            });

            // ========= GROUP GRADE from SUBGRADE =========
            const subgradeSelect = document.getElementById("subgrade_id");
            const hiddenGroupInput = document.getElementById("group_grade");

            function updateGroupGrade() {
                const selected = subgradeSelect.options[subgradeSelect.selectedIndex];
                hiddenGroupInput.value = selected?.dataset?.group ?? "";
            }
            updateGroupGrade();
            subgradeSelect.addEventListener("change", updateGroupGrade);

            // ========= DIVISION -> DEPT (AJAX like create) =========
            const currentDeptId = @json(old('departementid', $personnel->departementid ?? ''));

            function resetDept(message = 'Select Department') {
                $('#departementid').html(`<option value="" disabled selected>${message}</option>`);
                $('#departementid').trigger('change.select2');
            }

            function loadDepartments(divisionId, selectedDeptId = null) {
                $.ajax({
                    url: `/hr/departments`,
                    type: 'GET',
                    dataType: 'json',
                    data: { division_id: divisionId },
                    success: function(rows) {
                        let html = `<option value="" disabled>Select Department</option>`;

                        if (rows && rows.length) {
                            rows.forEach(function(r) {
                                const selected = String(r.department_id) === String(selectedDeptId) ? 'selected' : '';
                                html += `<option value="${r.department_id}" ${selected}>${r.department_name}</option>`;
                            });

                            $('#departementid').html(html).trigger('change.select2');
                        } else {
                            resetDept('No department found');
                        }
                    },
                    error: function() {
                        resetDept('Error loading department');
                    }
                });
            }

            $('#division_id').on('change', function() {
                const divisionId = $(this).val();
                if (!divisionId) {
                    resetDept();
                    return;
                }

                loadDepartments(divisionId, null);
            });

            // ========= COMPANY -> SITE (AJAX like create) =========
            const currentSiteValue = @json($personnel->locationname); // samakan dengan yang kamu simpan (id/site)
            const isSby = @json(!empty($isSby));
            const companyBudgets = @json($companyBudgets ?? []);
            const currentBudgetValue = @json(old('budget_entity_id', $personnel->budget_entity_id));

            function loadCompanyBudgets(cpnyid, selectedValue = null) {
                if (!isSby) return;

                const $budget = $('#budget_entity_id');
                $budget.empty().append('<option value="">Select Budget Company</option>');

                const matchingBudgets = companyBudgets
                    .filter(item => String(item.cpnyid) === String(cpnyid));

                matchingBudgets.forEach(item => {
                        const label = item.budget_entity_name
                            ? `${item.budget_entity_id} (${item.budget_entity_name})`
                            : item.budget_entity_id;
                        const option = new Option(label, item.budget_entity_id);
                        $budget.append(option);
                    });

                if (matchingBudgets.length === 1) {
                    $budget.val(String(matchingBudgets[0].budget_entity_id));
                } else if (selectedValue !== null && selectedValue !== '') {
                    $budget.val(String(selectedValue));
                }

                $budget.trigger('change');
            }

            function loadSites(cpnyid, selectedValue) {
                const $site = $('#siteid');

                if (!cpnyid) {
                    $site.html('<option value="">Select Site </option>');
                    return;
                }

                $.getJSON(`/api/sites/${cpnyid}`, function(data) {
                    $site.empty().append('<option value="">Select Site </option>');

                    data.forEach(function(row) {
                        // kalau API kamu return: {site:"xxx"} tanpa id, pakai row.site untuk value
                        const value = row.id ?? row.site;
                        const text = row.site ?? row.locationname ?? value;

                        const isSelected = String(value) === String(selectedValue);
                        $site.append(new Option(text, value, false, isSelected));
                    });

                    if (selectedValue) $site.val(String(selectedValue)).trigger('change');
                });
            }

            loadSites($('#cpnyid').val(), currentSiteValue);
            loadCompanyBudgets($('#cpnyid').val(), currentBudgetValue);
            $('#cpnyid').on('change', function() {
                loadSites(this.value, null);
                loadCompanyBudgets(this.value, null);
            });

            // ========= ATTACHMENT (add/remove) =========
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-900/30">
                        <input type="file" name="attachments[]"
                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                        <button type="button" class="removeAttachment flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
                    </div>
                `);
                toggleDeleteAttachmentButton();
            });

            $(document).on('click', '.removeAttachment', function() {
                $(this).closest('.attachment-row').remove();
                toggleDeleteAttachmentButton();
            });

            function toggleDeleteAttachmentButton() {
                // hanya untuk baris upload baru (removeAttachment)
                const uploadRows = $('#attachmentsContainer .removeAttachment');
                if (uploadRows.length > 1) uploadRows.removeClass('hidden');
                else uploadRows.addClass('hidden');
            }
            toggleDeleteAttachmentButton();

            // remove existing attachment (removeAttachment2)
            $(document).on('click', '.removeAttachment2', function() {
                let attachmentId = $(this).data('id');
                let row = $(this).closest('.attachment-row');

                if (!confirm('Are you sure you want to remove this attachment?')) return;

                $.ajax({
                    url: "/personnels/remove-attachment/" + attachmentId,
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();
                            toastr.success("Attachment removed successfully!");
                        } else {
                            toastr.error("Failed to remove attachment.");
                        }
                    },
                    error: function(xhr) {
                        toastr.error("Error! Unable to remove attachment.");
                        console.error(xhr.responseText);
                    }
                });
            });

            function toggleReplacementField() {
                let jobType = $('#job_type').val();

                if (jobType === 'Replacement') {
                    $('#replacementField')
                        .removeClass('hidden')
                        .addClass('flex');

                    $('#immediate_replacement').attr('required', true);

                } else {
                    $('#replacementField')
                        .addClass('hidden')
                        .removeClass('flex');

                    $('#immediate_replacement').val('');
                    $('#immediate_replacement').removeAttr('required');
                }
            }

            $('#job_type').on('change', toggleReplacementField);

            // 🔥 IMPORTANT (EDIT MODE)
            toggleReplacementField();

            $(document).on('keydown', 'input, textarea', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    return false;
                }
            });

            // ========= RESPONSIBILITIES add/remove like create (hide delete if 1) =========
            function updateResponsibilitiesRemoveButtons() {
                if ($('.responsibilities-row').length > 1) $('.removeResponsibilities').removeClass('hidden');
                else $('.removeResponsibilities').addClass('hidden');
            }

            function renumberResponsibilities() {
                $('#responsibilitiesTable .responsibilities-row').each(function(i) {
                    $(this).find('.row-num').text(i + 1);
                });
            }

            updateResponsibilitiesRemoveButtons();

            let responsibilityCount = $('#responsibilitiesTable .responsibilities-row').length;

            $('#addResponsibilities').click(function() {
                responsibilityCount++;
                $('#responsibilitiesTable').append(`
                    <div class="responsibilities-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                        </span>
                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">${responsibilityCount}</span>
                        <input type="text" name="responsibilities[]" placeholder="Type here..." class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                        <button type="button" class="removeResponsibilities hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
                    </div>
                `);
                updateResponsibilitiesRemoveButtons();
            });

            $(document).on('click', '.removeResponsibilities', function() {
                $(this).closest('.responsibilities-row').remove();
                renumberResponsibilities();
                updateResponsibilitiesRemoveButtons();
            });

            // ========= QUALIFICATION add/remove like create (hide delete if 1) =========
            function updateQualificationRemoveButtons() {
                if ($('.qualification-row').length > 1) $('.removeQualification').removeClass('hidden');
                else $('.removeQualification').addClass('hidden');
            }

            function renumberQualification() {
                $('#qualificationTable .qualification-row').each(function(i) {
                    $(this).find('.row-num').text(i + 1);
                });
            }

            updateQualificationRemoveButtons();

            let qualificationCount = $('#qualificationTable .qualification-row').length;

            $('#addQualification').click(function() {
                qualificationCount++;
                $('#qualificationTable').append(`
                    <div class="qualification-row flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-700 dark:bg-gray-900/30">
                        <span class="row-grip shrink-0 text-gray-300 dark:text-gray-600">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="6" r="1.4"/><circle cx="16" cy="6" r="1.4"/><circle cx="8" cy="12" r="1.4"/><circle cx="16" cy="12" r="1.4"/><circle cx="8" cy="18" r="1.4"/><circle cx="16" cy="18" r="1.4"/></svg>
                        </span>
                        <span class="row-num flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-gray-300 bg-white text-xs font-bold text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400">${qualificationCount}</span>
                        <input type="text" name="qualification[]" placeholder="Type here..." class="flex-1 border-none bg-transparent p-1 text-sm text-gray-700 focus:outline-none focus:ring-0 dark:text-gray-200">
                        <button type="button" class="removeQualification hidden flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 7h16M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m-8 0l1 12a1 1 0 001 1h6a1 1 0 001-1l1-12"/></svg>
                        </button>
                    </div>
                `);
                updateQualificationRemoveButtons();
            });

            $(document).on('click', '.removeQualification', function() {
                $(this).closest('.qualification-row').remove();
                renumberQualification();
                updateQualificationRemoveButtons();
            });

            // ========= number-only guard (optional) =========
            $('.number-only').on('keypress', function(event) {
                let charCode = event.which ? event.which : event.keyCode;
                if (charCode < 48 || charCode > 57) event.preventDefault();
            }).on('input', function() {
                $(this).val(String($(this).val()).replace(/[^0-9]/g, ''));
            });

            // total_actual readonly
            $('#total_actual').prop('readonly', true);
            function calculateTotalActual() {
                const actual = parseInt($('#actual').val() || 0, 10);
                const required = parseInt($('#required').val() || 0, 10);

                $('#total_actual').val(actual + required);
            }

            $('#actual, #required').on('input change keyup', function() {
                calculateTotalActual();
            });

            calculateTotalActual();

            // ========= Job Type segmented toggle: mirrors clicks onto the real #job_type select =========
            function syncJobTypeSegmented() {
                let val = $('#job_type').val();
                $('#jobTypeSegmented .seg-btn').each(function() {
                    let active = $(this).data('value') === val;
                    $(this)
                        .toggleClass('bg-white shadow-sm text-indigo-700 dark:bg-gray-800 dark:text-indigo-300', active)
                        .toggleClass('text-gray-500 dark:text-gray-300', !active);
                });
            }

            $('#jobTypeSegmented .seg-btn').on('click', function() {
                $('#job_type').val($(this).data('value')).trigger('change');
            });

            $('#job_type').on('change', syncJobTypeSegmented);
            syncJobTypeSegmented();
        });
    </script>
</x-app-layout>
