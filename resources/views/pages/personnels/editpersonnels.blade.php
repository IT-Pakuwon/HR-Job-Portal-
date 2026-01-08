<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Edit Personnel Requisition -
                                {{ $personnel->docid ?? '' }}
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $personnel->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Division</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="division" required>
                                    <option value="" disabled>Select Division</option>
                                    @foreach ($division as $p)
                                        <option value="{{ $p->division_id }}"
                                            {{ $p->division_id == $personnel->division_id ? 'selected' : '' }}>
                                            {{ $p->division_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="departementid" required>
                                    @foreach ($departements as $p)
                                        <option value="{{ $p->deptname }}"
                                            {{ $p->deptname == $personnel->departementid ? 'selected' : '' }}>
                                            {{ $p->deptname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Lokasi
                                    Kerja</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="siteid" id="siteid" required>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Detail Info</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="pt-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Type</label>
                                        <select name="job_type" id="job_type"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="New"
                                                {{ old('job_type', $personnel->job_type ?? '') == 'New' ? 'selected' : '' }}>
                                                New</option>
                                            <option value="Replacement"
                                                {{ old('job_type', $personnel->job_type ?? '') == 'Replacement' ? 'selected' : '' }}>
                                                Replacement</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Title</label>
                                        {{-- <select name="job_title" id="job_title"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="">Select</option>
                                        </select> --}}
                                        <div>
                                            <input type="text" name="job_title" class="w-full rounded border p-2"
                                                value="{{ $personnel->job_title }}">
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Level</label>
                                        {{-- <input type="hidden" name="subgrade_id" id="subgrade_id">
                                        <input type="text" name="job_level" id="job_level"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly> --}}
                                        <select
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            name="subgrade_id" id="subgrade_id" required>
                                            @foreach ($subgradings as $sg)
                                                <option value="{{ $sg->subgrade_id }}" @selected((string) $sg->subgrade_id === (string) old('subgrade_id', $personnel->subgrade_id ?? ''))>
                                                    {{ $sg->subgrade_id }}-{{ $sg->subgrade_name }}
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
                                            value="{{ old('immediate_superior', $personnel->immediate_superior ?? '') }}">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State
                                            Position</label>
                                        <input type="text" name="state_position" id="state_position"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            value="{{ old('state_position', $personnel->state_position ?? '') }}">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason
                                            for Vacancy</label>
                                        <textarea name="reason_vacancy" id="reason_vacancy"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>{{ old('reason_vacancy', $personnel->reason_vacancy ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div
                                    class="mt-8 grid grid-cols-1 gap-6 rounded-lg bg-gray-100/40 p-6 sm:grid-cols-3 dark:bg-gray-700/40">
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="number" name="actual" id="actual" min="0"
                                            value={{ $personnel->actual }}
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number
                                            Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            value={{ $personnel->required }}
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                            Actual
                                            Number</label>
                                        <input type="number" name="total_actual" id="total_actual" min="0"
                                            value={{ $personnel->total_actual }}
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-lg font-semibold">Job Responsibilities</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>
                                <div class="flex h-auto flex-col justify-start">
                                    <div class="overflow-y-auto">
                                        <table class="mb-10 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="border p-3">Responsibility</th>
                                                    <th class="w-16 border p-3 text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="responsibilitiesTable">
                                                @foreach ($jobres as $key => $resp)
                                                    <tr>
                                                        <td class="border p-3 text-center">{{ $key + 1 }}
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" placeholder="Type here..."
                                                                name="responsibilities[]"
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                value="{{ $resp->job_responsibilities_descr }}">
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeResponsibilities rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" id="addResponsibilities"
                                        class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
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
                        <div class="border-b"></div>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="flex w-full flex-col">
                            <details class="group" open>
                                <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                    <span class="text-lg font-semibold">Job Qualification</span>
                                    <span class="transition-all group-open:hidden">See details</span>
                                    <span class="hidden transition-all group-open:inline">Hide details</span>
                                </summary>
                                <!-- Education -->
                                <div class="flex flex-col gap-2">
                                    <label class="font-semibold">🔹 Education</label>
                                    <div class="relative mb-4">
                                        <select name="education" id="education"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            <option value=""
                                                {{ $personnel->education == '' ? 'selected' : '' }}></option>
                                            <option value="SMP"
                                                {{ $personnel->education == 'SMP' ? 'selected' : '' }}>SMP
                                            </option>
                                            <option
                                                value="SMA / SMK"{{ $personnel->education == 'SMA / SML' ? 'selected' : '' }}>
                                                SMA / SMK</option>
                                            <option value="D1"
                                                {{ $personnel->education == 'D1' ? 'selected' : '' }}>D1
                                            </option>
                                            <option value="D2"
                                                {{ $personnel->education == 'D2' ? 'selected' : '' }}>D2
                                            </option>
                                            <option
                                                value="D3"{{ $personnel->education == 'D3' ? 'selected' : '' }}>
                                                D3</option>
                                            <option
                                                value="D4"{{ $personnel->education == 'D4' ? 'selected' : '' }}>
                                                D4</option>
                                            <option
                                                value="S1"{{ $personnel->education == 'S1' ? 'selected' : '' }}>
                                                S1</option>
                                            <option
                                                value="S2"{{ $personnel->education == 'S2' ? 'selected' : '' }}>
                                                S2</option>
                                            <option
                                                value="S3"{{ $personnel->education == 'S3' ? 'selected' : '' }}>
                                                S3</option>
                                        </select>
                                    </div>
                                    <div class="border-b"></div>
                                </div>

                                <div class="mt-4 flex flex-col gap-2">
                                    <label class="font-semibold">🔹 Experience</label>
                                    <div class="mb-4 flex gap-4">
                                        <div class="w-1/2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">Start</label>
                                            <input type="number" name="experience_start" id="experience_start"
                                                value="{{ $personnel->experience_start }}" min="0"
                                                placeholder="Input here"
                                                class="mt-2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                        </div>
                                        <div class="w-1/2">
                                            <label class="font-medium text-gray-700 dark:text-gray-300">End</label>
                                            <input type="number" name="experience_end" id="experience_end"
                                                value="{{ $personnel->experience_end }}" min="0"
                                                placeholder="Input here"
                                                class="mt-2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                        </div>
                                    </div>
                                    <div class="border-b"></div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Tags</label>

                                    @php
                                        // Kumpulan tag terpilih (dari TrJobtag atau old())
                                        $selected = collect(old('tags', $selectedTags ?? []))
                                            ->filter(fn($t) => filled($t))
                                            ->map(fn($t) => (string) trim($t))
                                            ->unique()
                                            ->values()
                                            ->toArray();

                                        // Master (opsional, dari MJobtag)
                                        $master = collect($skillTags ?? [])
                                            ->pluck('job_tags')
                                            ->filter(fn($t) => filled($t))
                                            ->map(fn($t) => (string) trim($t));

                                        // Gabungkan supaya tag yg tidak ada di master tetap tampil
                                        $allTags = $master->merge($selected)->unique()->sort()->values();
                                    @endphp

                                    <select name="tags[]" id="tags" multiple
                                        class="tags-input w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        @foreach ($allTags as $tag)
                                            <option value="{{ $tag }}"
                                                {{ in_array($tag, $selected, true) ? 'selected' : '' }}>
                                                {{ $tag }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>



                                <div class="mt-4 flex flex-col gap-2">
                                    <label class="font-semibold">🔹 Skills</label>
                                    <div class="overflow-y-auto">
                                        <table class="mb-4 mt-3 w-full">
                                            <thead class="bg-gray-100/10">
                                                <tr>
                                                    <th class="w-12 border p-3 text-center">No</th>
                                                    <th class="border p-3">Skill</th>
                                                    <th class="w-16 border p-3 text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="qualificationTable">
                                                @foreach ($jobqua as $key => $qua)
                                                    <tr>
                                                        <td class="border p-3 text-center">{{ $key + 1 }}
                                                        </td>
                                                        <td class="border p-3">
                                                            <input type="text" name="qualification[]"
                                                                placeholder="Type here..."
                                                                class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                value="{{ $qua->job_qualification_descr }}">
                                                        </td>
                                                        <td class="border p-3 text-center">
                                                            <button type="button"
                                                                class="removeQualification rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" id="addQualification"
                                        class="mb-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-indigo-700 hover:bg-indigo-200/10 hover:font-medium hover:text-indigo-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg> Add Column
                                    </button>
                                </div>
                            </details>
                        </div>
                        <div class="border-b"></div>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        {{-- <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    @foreach ($attachment as $attach)
                                        <div class="attachment-row flex items-center gap-2"
                                            data-attachid="{{ $attach->id }}">
                                            <a href="{{ url('/attachments/' . $attach->attachfile) }}"
                                                target="_blank" class="mt-4 w-full border p-3 text-lg">📎
                                                {{ $attach->name }}</a>
                                            <button type="button"
                                                class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                data-id="{{ $attach->id }}">🗑️
                                            </button>
                                        </div>
                                    @endforeach
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
                        </details> --}}
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>

                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    @foreach ($attachment as $attach)
                                        @php
                                            $fileUrl = route('attachments.view', ['id' => $attach->id]);
                                        @endphp
                                        <div class="attachment-row flex items-center gap-2"
                                            data-attachid="{{ $attach->id }}">
                                            <a href="{{ $fileUrl }}" target="_blank"
                                                class="mt-4 w-full border p-3 text-lg">
                                                📎 {{ $attach->attachment_name ?? basename($attach->filename) }}
                                            </a>
                                            <button type="button"
                                                class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                data-id="{{ $attach->id }}">🗑️
                                            </button>
                                        </div>
                                    @endforeach
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

                        <div class="grid grid-cols-2 justify-between gap-4 md:flex md:flex-row xl:justify-end">
                            <div class="flex justify-start">
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

                            <div class="flex justify-start md:justify-end">
                                <button type="button" id="cancelBtn"
                                    class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
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
                Personnel Requisition Created Successfully!
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                // let personnelId = "{{ $personnel->id }}"; // pastikan ID tersedia di view
                let personnelHash = @json($hash);
                let updateUrl = `/personnels/${personnelHash}`;

                // Tampilkan Loading, Disable Button
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
                if (confirmed) {
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
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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

            $(document).on('click', '.removeAttachment2', function() {
                let attachmentId = $(this).data('id'); // Ambil ID attachment
                let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

                // Cek konfirmasi pengguna
                let confirmDelete = confirm('Are you sure you want to remove this attachment?');

                if (confirmDelete) {
                    $.ajax({
                        url: "/personnels/remove-attachment/" +
                            attachmentId, // Endpoint ke controller
                        type: "POST",
                        data: {
                            _method: "PUT",
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                row.remove(); // Hapus dari tampilan jika berhasil
                                alert("Attachment removed successfully!");
                            } else {
                                alert("Failed to remove attachment.");
                            }
                        },
                        error: function(xhr) {
                            alert("Error! Unable to remove attachment.");
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    // **TIDAK ADA AKSI JIKA USER MEMBATALKAN**
                    return false;
                }
            });
        });
    </script>


    <script>
        // Add Responsibility
        $('#addResponsibilities').click(function() {
            let rowCount = $('#responsibilitiesTable tr').length + 1;
            $('#responsibilitiesTable').append(`
                <tr>
                    <td class="p-3 border text-center">${rowCount}</td>
                    <td class="p-3 border">
                        <input type="text" placeholder="Type here..."  name="responsibilities[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeResponsibilities  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded">🗑️</button>
                    </td>
                </tr>
            `);
        });

        // Remove Responsibility
        $(document).on('click', '.removeResponsibilities', function() {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        // Update row numbers after deleting
        function updateRowNumbers() {
            $('#responsibilitiesTable tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }

        // Add Qualification
        $('#addQualification').click(function() {
            let rowCount = $('#qualificationTable tr').length + 1;
            $('#qualificationTable').append(`
                <tr>
                    <td class="p-3 border text-center">${rowCount}</td>
                    <td class="p-3 border">
                        <input type="text" name="qualification[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    <td class="p-3 border text-center">
                        <button type="button" class="removeQualification  bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded">🗑️</button>
                    </td>
                </tr>
            `);
        });

        // Remove Responsibility
        $(document).on('click', '.removeQualification', function() {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        // Update row numbers after deleting
        function updateRowNumbers() {
            $('#qualificationTable tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
        }
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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

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
        $(function() {
            const $tags = $('#tags');
            const selectedTags = @json($selected ?? []);

            // Jika ada tag terpilih yang belum ada sebagai <option>, tambahkan (aman untuk tags: true)
            selectedTags.forEach(t => {
                if ($tags.find('option[value="' + t.replace(/"/g, '\\"') + '"]').length === 0) {
                    $tags.append(new Option(t, t, true, true));
                }
            });

            // Init Select2 TANPA 'data:' agar tidak menimpa <option> HTML
            $tags.select2({
                placeholder: "Select or type tags",
                tags: true,
                tokenSeparators: [',']
            });

            // Pastikan nilai ter-set (kalau Select2 re-init di tempat lain)
            $tags.val(selectedTags).trigger('change');
        });
    </script>
    <script>
        $(function() {
            const $cpny = $('select[name="cpnyid"]');
            const $site = $('select[name="siteid"]');

            // Pakai ID yg tersimpan di DB, bukan locationname
            const currentSiteId = @json($personnel->locationname); // <-- penting: ID, bukan nama

            function loadSites(cpnyid, selectedId) {
                if (!cpnyid) {
                    $site.html('<option value="">-- Select Site --</option>');
                    return;
                }

                $.getJSON(`/api/sites/${cpnyid}`, function(data) {
                    $site.empty().append('<option value="">-- Select Site --</option>');

                    // Pastikan key yg dipakai sesuai response API kamu (id & site)
                    data.forEach(function(row) {
                        // new Option(text, value, defaultSelected, selected)
                        const isSelected = String(row.id) === String(selectedId);
                        $site.append(new Option(row.site, row.id, false, isSelected));
                    });

                    // “Double set” untuk memastikan value benar-benar terpilih
                    if (selectedId) {
                        $site.val(String(selectedId)).trigger('change');
                    }
                });
            }

            // Initial fill saat halaman dibuka
            loadSites($cpny.val(), currentSiteId);

            // Saat company berubah, muat ulang sites (tanpa preselect lama)
            $cpny.on('change', function() {
                loadSites(this.value, null);
            });

            // Debug cepat: lihat apa yg terkirim saat submit
            $('#personnelForm').on('submit', function() {
                const fd = new FormData(this);
                console.log('siteid payload =', fd.get('siteid')); // harusnya ID (string)
            });
        });
    </script>





</x-app-layout>
