<x-app-layout>


    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">

                <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data" method="POST">
                    @csrf

                    {{-- HEADER --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <div class="border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">
                                Edit Personnel Requisition - {{ $personnel->docid ?? '' }}
                            </h2>
                        </div>

                        <div class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            {{-- Company --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" id="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpny_id }}"
                                            {{ (string) $p->cpny_id === (string) $personnel->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpny_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Division --}}
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="division" id="division_id" required>
                                    <option value="" disabled>Select Division</option>
                                    @foreach ($division as $p)
                                        <option value="{{ $p->division_id }}"
                                            {{ (string) $p->division_id === (string) $personnel->division_id ? 'selected' : '' }}>
                                            {{ $p->division_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Department (AJAX loaded like create) --}}
                            {{-- <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="departementid" id="departementid" required>
                                    <option value="" disabled selected>Select Department</option>
                                </select>
                            </div> --}}
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
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Placement
                                    Location</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="siteid" id="siteid" required>
                                    <option value="">Select Site </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- JOB DETAIL INFO --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Detail Info</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details →</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details ↓</span>
                            </summary>

                            <div class="pt-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                                    <div id="jobTypeWrapper" class="grid grid-cols-1 md:grid-cols-1 gap-6 md:col-span-2">

                                    {{-- Job Type --}}
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Job Type
                                        </label>
                                        <select name="job_type" id="job_type"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="" disabled>Select Job Type</option>
                                            <option value="New" @selected(old('job_type', $personnel->job_type) === 'New')>New</option>
                                            <option value="Replacement" @selected(old('job_type', $personnel->job_type) === 'Replacement')>Replacement</option>
                                        </select>
                                    </div>

                                    {{-- Replacement --}}
                                    <div id="replacementField" class="hidden flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Replacement Name
                                        </label>
                                        <input type="text" name="immediate_replacement" id="immediate_replacement"
                                            value="{{ old('immediate_replacement', $personnel->immediate_replacement ?? '') }}"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            placeholder="Enter employee name to be replaced">
                                    </div>

                                </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Title</label>
                                        <input type="text" name="job_title" id="job_title"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            value="{{ old('job_title', $personnel->job_title) }}" required>
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Level</label>
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
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Immediate
                                            Superior</label>
                                        <input type="text" name="immediate_superior" id="immediate_superior"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            value="{{ old('immediate_superior', $personnel->immediate_superior) }}">
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State
                                            Position</label>
                                        <input type="text" name="state_position" id="state_position"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            value="{{ old('state_position', $personnel->state_position) }}">
                                    </div>

                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason
                                            for Vacancy</label>
                                        <textarea name="reason_vacancy" id="reason_vacancy"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>{{ old('reason_vacancy', $personnel->reason_vacancy) }}</textarea>
                                    </div>
                                </div>

                                <div
                                    class="mb-6 mt-6 grid grid-cols-1 gap-4 rounded-l bg-gray-200/40 p-4 sm:grid-cols-3">
                                    <div class="flex items-center gap-4">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="number" name="actual" id="actual" min="0"
                                            class="number-only w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ old('actual', $personnel->actual) }}" readonly>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Number
                                            Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            class="number-only w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ old('required', $personnel->required) }}" readonly>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Total
                                            Actual Number</label>
                                        <input type="number" name="total_actual" id="total_actual" min="0"
                                            class="number-only w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ old('total_actual', $personnel->total_actual) }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>

                    {{-- RESPONSIBILITIES --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white shadow-md dark:bg-gray-800">
                        <div class="flex w-full flex-col rounded-xl p-4">
                            <details class="group" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-sm font-semibold">Job Responsibilities</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>

                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-y-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="border-l border-t p-3">Responsibility</th>
                                                    <th class="w-16 border-r border-t p-3 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="responsibilitiesTable">
                                                @php $res = $jobres ?? collect(); @endphp
                                                @if ($res->count() === 0)
                                                    <tr class="responsibilities-row">
                                                        <td class="border p-3 text-center">1</td>
                                                        <td class="border p-3">
                                                            <input type="text" name="responsibilities[]"
                                                                placeholder="Type here..."
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeResponsibilities hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach ($res as $i => $resp)
                                                        <tr class="responsibilities-row">
                                                            <td class="border p-3 text-center">{{ $i + 1 }}
                                                            </td>
                                                            <td class="border p-3">
                                                                <input type="text" name="responsibilities[]"
                                                                    placeholder="Type here..."
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    value="{{ $resp->job_responsibilities_descr }}">
                                                            </td>
                                                            <td class="border p-3 text-center">
                                                                <button type="button"
                                                                    class="removeResponsibilities hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button" id="addResponsibilities"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Add Column
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- QUALIFICATION (same layout as create) --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white shadow-md dark:bg-gray-800">
                        <div class="flex w-full flex-col gap-4 p-4">
                            <details class="group w-full min-w-0 max-w-full px-1" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-sm font-semibold">Job Qualification</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>

                                {{-- Education --}}
                                <div class="flex flex-col gap-2">
                                    <label class="mb-2 font-semibold"> 🔹 Education</label>
                                    <div class="relative pl-4">
                                        <select name="education" id="education"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                            <option value="" disabled
                                                {{ old('education', $personnel->education) ? '' : 'selected' }}>Select
                                            </option>
                                            @foreach (['SMP', 'SMA / SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'] as $edu)
                                                <option value="{{ $edu }}" @selected(old('education', $personnel->education) === $edu)>
                                                    {{ $edu }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Experience --}}
                                <div class="flex flex-col gap-2 pb-4 pt-4">
                                    <label class="mb-2 font-semibold"> 🔹 Experience</label>
                                    <div class="flex gap-4 pl-4">
                                        <div class="flex w-1/2 flex-col">
                                            <label
                                                class="mb-2 font-medium text-gray-700 dark:text-gray-300">Start</label>
                                            <input type="number" name="experience_start" id="experience_start"
                                                min="0"
                                                value="{{ old('experience_start', $personnel->experience_start) }}"
                                                placeholder="Input here"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        </div>
                                        <div class="flex w-1/2 flex-col">
                                            <label
                                                class="mb-2 font-medium text-gray-700 dark:text-gray-300">End</label>
                                            <input type="number" name="experience_end" id="experience_end"
                                                min="0"
                                                value="{{ old('experience_end', $personnel->experience_end) }}"
                                                placeholder="Input here"
                                                class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        </div>
                                    </div>
                                </div>

                                {{-- Skills table --}}
                                <div class="flex h-auto flex-col justify-start">
                                    <label class="mb-2 font-semibold"> 🔹 Skill</label>
                                    <div class="overflow-y-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="border-t p-3">Skill</th>
                                                    <th class="w-16 border-r border-t p-3 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="qualificationTable">
                                                @php $qua = $jobqua ?? collect(); @endphp
                                                @if ($qua->count() === 0)
                                                    <tr class="qualification-row">
                                                        <td class="border p-3 text-center">1</td>
                                                        <td class="border p-3">
                                                            <input type="text" name="qualification[]"
                                                                placeholder="Type here..."
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeQualification hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @else
                                                    @foreach ($qua as $i => $q)
                                                        <tr class="qualification-row">
                                                            <td class="border p-3 text-center">{{ $i + 1 }}
                                                            </td>
                                                            <td class="border p-3">
                                                                <input type="text" name="qualification[]"
                                                                    placeholder="Type here..."
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    value="{{ $q->job_qualification_descr }}">
                                                            </td>
                                                            <td class="border p-3 text-center">
                                                                <button type="button"
                                                                    class="removeQualification hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30">🗑️</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <button type="button" id="addQualification"
                                        class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg> Add Column
                                    </button>
                                </div>

                                {{-- Tags --}}
                                <div class="mt-4 w-full min-w-0">
                                    <label
                                        class="mb-2 flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-200">
                                        <span>🔹</span><span>Tags</span>
                                    </label>

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
                                        class="tags-input block w-full min-w-0 rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                        @foreach ($allTags as $tag)
                                            <option value="{{ $tag }}"
                                                {{ in_array($tag, $selected, true) ? 'selected' : '' }}>
                                                {{ $tag }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- ATTACHMENTS --}}
                    <div class="flex w-full flex-col gap-2 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details →</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details ↓</span>
                            </summary>

                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    @foreach ($attachment as $attach)
                                        @php $fileUrl = route('attachments.view', ['id' => $attach->id]); @endphp
                                        <div class="attachment-row flex items-center gap-2"
                                            data-attachid="{{ $attach->id }}">
                                            <a href="{{ $fileUrl }}" target="_blank"
                                                class="mt-4 w-full border p-3 text-sm">
                                                📎 {{ $attach->attachment_name ?? basename($attach->filename) }}
                                            </a>
                                            <button type="button"
                                                class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                data-id="{{ $attach->id }}">🗑️</button>
                                        </div>
                                    @endforeach

                                    {{-- baris upload baru minimal 1 --}}
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="file: flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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

                            <div class="flex flex-col gap-3 md:flex-row md:items-center">
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
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                Personnel Requisition Updated Successfully!
            </div>
        </div>
    </div>

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
                $('#loadingSpinner').removeClass('hidden');

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
            // const currentDeptId = @json($personnel->departementid);

            // function resetDept(message = 'Select Department') {
            //     $('#departementid').empty().append(`<option value="" disabled selected>${message}</option>`);
            //     $('#departementid').val(null).trigger('change');
            // }

            // function loadDepartments(divisionId, selectedDeptId = null) {
            //     resetDept('Loading...');
            //     $.ajax({
            //         url: `/hr/departments`,
            //         type: 'GET',
            //         dataType: 'json',
            //         data: {
            //             division_id: divisionId
            //         },
            //         success: function(rows) {
            //             resetDept('Select Department');

            //             if (rows && rows.length) {
            //                 rows.forEach(r => {
            //                     $('#departementid').append(
            //                         `<option value="${r.department_id}">${r.department_name}</option>`
            //                     );
            //                 });

            //                 if (selectedDeptId) {
            //                     $('#departementid').val(String(selectedDeptId)).trigger('change');
            //                 }
            //             } else {
            //                 resetDept('No department found');
            //             }
            //         },
            //         error: function() {
            //             resetDept('Error loading department');
            //         }
            //     });
            // }

            const currentDeptId = @json(old('departementid', $personnel->departementid ?? ''));

            function resetDept(message = 'Select Department') {
                $('#departementid').html(`<option value="" disabled selected>${message}</option>`);
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
                            rows.forEach(r => {
                                const selected = String(r.department_id) === String(selectedDeptId) ? 'selected' : '';
                                html += `<option value="${r.department_id}" ${selected}>${r.department_name}</option>`;
                            });
                            $('#departementid').html(html).trigger('change.select2');
                        } else {
                            resetDept('No department found');
                            $('#departementid').trigger('change.select2');
                        }
                    },
                    error: function() {
                        resetDept('Error loading department');
                        $('#departementid').trigger('change.select2');
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

            $('#division_id').on('change', function() {
                const divisionId = $(this).val();
                if (!divisionId) return resetDept();
                loadDepartments(divisionId, null);
            });

            // init load dept based on selected division + preselect dept
            if ($('#division_id').val()) {
                loadDepartments($('#division_id').val(), currentDeptId);
            } else {
                resetDept();
            }

            // ========= COMPANY -> SITE (AJAX like create) =========
            const currentSiteValue = @json($personnel->locationname); // samakan dengan yang kamu simpan (id/site)
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
            $('#cpnyid').on('change', function() {
                loadSites(this.value, null);
            });

            // ========= ATTACHMENT (add/remove) =========
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
                    <div class="attachment-row flex items-center gap-2">
                        <input type="file" name="attachments[]"
                            class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                        <button type="button"
                            class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
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

            $(document).ready(function () {

                function toggleReplacementField() {
                    let jobType = $('#job_type').val();

                    if (jobType === 'Replacement') {

                        $('#jobTypeWrapper')
                            .removeClass('md:grid-cols-1')
                            .addClass('md:grid-cols-2');

                        $('#replacementField')
                            .removeClass('hidden')
                            .addClass('flex');

                        $('#immediate_replacement').attr('required', true);

                    } else {

                        $('#jobTypeWrapper')
                            .removeClass('md:grid-cols-2')
                            .addClass('md:grid-cols-1');

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

            });

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
                    $(this).find('td:first').text(i + 1);
                });
            }

            updateResponsibilitiesRemoveButtons();

            $('#addResponsibilities').click(function() {
                const next = $('#responsibilitiesTable .responsibilities-row').length + 1;
                $('#responsibilitiesTable').append(`
                    <tr class="responsibilities-row">
                        <td class="p-3 border text-center">${next}</td>
                        <td class="p-3 border">
                            <input type="text" name="responsibilities[]" placeholder="Type here..."
                                class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                        </td>
                        <td class="p-3 border text-center">
                            <button type="button"
                                class="removeResponsibilities rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 hidden">🗑️</button>
                        </td>
                    </tr>
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
                    $(this).find('td:first').text(i + 1);
                });
            }

            updateQualificationRemoveButtons();

            $('#addQualification').click(function() {
                const next = $('#qualificationTable .qualification-row').length + 1;
                $('#qualificationTable').append(`
                    <tr class="qualification-row">
                        <td class="p-3 border text-center">${next}</td>
                        <td class="p-3 border">
                            <input type="text" name="qualification[]" placeholder="Type here..."
                                class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                        </td>
                        <td class="p-3 border text-center">
                            <button type="button"
                                class="removeQualification rounded border border-red-700 bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden dark:bg-red-700/30">🗑️</button>
                        </td>
                    </tr>
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
        });
    </script>
</x-app-layout>
