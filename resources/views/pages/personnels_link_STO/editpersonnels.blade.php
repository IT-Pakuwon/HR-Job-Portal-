<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="personnelForm" enctype="multipart/form-data" method="POST">
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
                                    @foreach ($userdept as $p)
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
                                        <select name="job_title" id="job_title"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job
                                            Level</label>
                                        <input type="hidden" name="subgrade_id" id="subgrade_id">
                                        <input type="text" name="job_level" id="job_level"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
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
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number
                                            Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                            Actual
                                            Number</label>
                                        <input type="number" name="total_actual" id="total_actual" min="0"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Responsibilities</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-60 flex-col overflow-y-auto pt-6">
                                <table id="jobProfileTable"
                                    class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            <th
                                                class="w-10 border border-gray-200 p-3 text-center text-sm font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                No</th>
                                            <th
                                                class="border border-gray-200 p-3 text-left text-sm font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                Job Purpose</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-xl font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Qualification</span>
                                <span class="text-sm font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-sm font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-6 pt-6">
                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Education</label>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <input type="text" name="education" id="education_min"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                        <input type="text" name="education_jurusan" id="education_jurusan"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Experience</label>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <input type="text" name="experience_start" id="experience_start"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                        <input type="text" name="experience_position" id="experience_position"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Tags</label>
                                    <select name="tags[]" id="tags" multiple
                                        class="tags-input w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    </select>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-sm font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Skill</label>
                                    <div class="max-h-60 overflow-y-auto">
                                        <table
                                            class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                                            <thead>
                                                <tr class="bg-gray-50 dark:bg-gray-700">
                                                    <th
                                                        class="w-10 border border-gray-200 p-3 text-center text-sm font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                        No</th>
                                                    <th
                                                        class="border border-gray-200 p-3 text-left text-sm font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                        Skill</th>
                                                    <th
                                                        class="w-16 border border-gray-200 p-3 text-center text-sm font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="qualificationTable">
                                                <tr class="qualification-row">
                                                    <td
                                                        class="border border-gray-200 p-3 text-center dark:border-gray-700">
                                                        1</td>
                                                    <td class="border border-gray-200 p-3 dark:border-gray-700">
                                                        <input type="text" name="qualification[]"
                                                            placeholder="Type here..."
                                                            class="w-full border-none bg-transparent p-1 focus:outline-none focus:ring-0">
                                                    </td>
                                                    <td
                                                        class="border border-gray-200 p-3 text-center dark:border-gray-700">
                                                        <button type="button"
                                                            class="removeQualification hidden h-8 w-8 items-center justify-center rounded-md bg-red-100 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm2 3a1 1 0 011-1h4a1 1 0 110 2H10a1 1 0 01-1-1zm0 3a1 1 0 011-1h4a1 1 0 110 2H10a1 1 0 01-1-1z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" id="addQualification"
                                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                clip-rule="evenodd" />
                                        </svg> Add Skill
                                    </button>
                                </div>
                            </div>
                        </details>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
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
                        </details>

                        <div class="grid grid-cols-2 justify-between gap-4 md:flex md:flex-row xl:justify-end">
                            <!-- Cancel Button-->
                            <div class="flex justify-start">
                                <button id="cancelBtn"
                                    class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
                                    <span id="cancelText">Cancel</span>
                                    <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4">
                                        </circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex justify-start md:justify-end">
                                <button type="submit" id="submitBtn"
                                    class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
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
                Personnel Requisition Created Successfully!
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let personnelId = "{{ $personnel->id }}"; // pastikan ID tersedia di view
                let updateUrl = `/personnels/${personnelId}`;

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
        $(document).ready(function() {
            let responsibilityCount = 1;

            // Fungsi untuk Menambah Baris Responsibility
            $('#addResponsibilities').click(function() {
                responsibilityCount++;
                $('#responsibilitiesTable').append(`
                <tr class="responsibilities-row">
                    <td class="p-3 border text-center">${responsibilityCount}</td>
                    <td class="p-3 border">
                                <input type="text" name="responsibilities[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeResponsibilities  bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                    </td>
                </tr>
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
                $('#responsibilitiesTable tr').each(function() {
                    responsibilityCount++;
                    $(this).find('td:first').text(responsibilityCount);
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
            // Hitung jumlah baris existing saat halaman dimuat
            let qualificationCount = $('#qualificationTable .qualification-row').length || 0;

            $('#addQualification').click(function() {
                // Hitung ulang total baris yang ada saat ini (termasuk baris dari data AJAX)
                let qualificationCount = $('#qualificationTable tr.qualification-row').length + 1;

                $('#qualificationTable').append(`
                    <tr class="qualification-row">
                        <td class="p-3 border text-center">${qualificationCount}</td>
                        <td class="p-3 border">
                            <input type="text" name="qualification[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                        </td>
                        <td class="p-3 border text-center">
                            <button type="button" class="removeQualification bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded">🗑️</button>
                        </td>
                    </tr>
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
                $('#qualificationTable .qualification-row').each(function(index) {
                    qualificationCount++;
                    $(this).find('td:first').text(qualificationCount);
                });
            }

            // Fungsi untuk Menyembunyikan Tombol Hapus Jika Hanya Satu Baris
            function updateRemoveButtons() {
                if ($('#qualificationTable .qualification-row').length > 1) {
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
        $(document).ready(function() {
            // Ambil siteid yang sedang diedit dari backend
            var currentSiteId = "{{ $personnel->locationname }}";
            console.log("Current Site ID:", currentSiteId);
            // Fungsi ketika Company berubah
            $('select[name="cpnyid"]').on('change', function() {
                var cpnyid = $(this).val();

                if (cpnyid) {
                    $.ajax({
                        url: `/api/sites/${cpnyid}`,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            console.log("Received sites data:", data);
                            let $siteSelect = $('select[name="siteid"]');
                            $siteSelect.empty();
                            $siteSelect.append('<option value="">-- Select Site --</option>');

                            $.each(data, function(key, value) {
                                // console.log("Adding site option:", value);
                                // console.log("Current Site ID:", currentSiteId);
                                $siteSelect.append(
                                    `<option value="${value.id}" ${(value.site == currentSiteId) ? 'selected' : ''}>${value.site}</option>`
                                );
                            });
                        }
                    });
                } else {
                    $('select[name="siteid"]').empty().append(
                        '<option value="">-- Select Site --</option>');
                }
            });

            // 🔄 Trigger langsung saat load untuk mengisi default site
            $('select[name="cpnyid"]').trigger('change');
        });
    </script>


    <script>
        $(document).ready(function() {
            // $('select[name="departementid"]').on('change', function() {
            //     let deptId = $(this).val();
            //     let $jobTitle = $('#job_title');
            //     $jobTitle.empty().append('<option value="">Loading...</option>');

            //     if (deptId) {
            //         $.ajax({
            //             url: `/api/vacant-employees/${deptId}`,
            //             type: 'GET',
            //             dataType: 'json',
            //             success: function(data) {

            //                 $jobTitle.empty().append(
            //                     '<option value="">-- Select Vacant Position --</option>');
            //                 if (data.length > 0) {
            //                     $.each(data, function(key, emp) {
            //                         $jobTitle.append(
            //                             `<option value="${emp.departement_id}" data-title-level="${emp.subgrade_name}" data-parent-id="${emp.parent_id}">${emp.departement_name}-${emp.subgrade_name}</option>`
            //                         );
            //                     });
            //                 } else {
            //                     $jobTitle.append('<option value="">No vacant found</option>');
            //                 }
            //             },
            //             error: function() {
            //                 $jobTitle.empty().append(
            //                     '<option value="">Error loading data</option>');
            //             }
            //         });
            //     } else {
            //         $jobTitle.empty().append('<option value="">-- Select Vacant Position --</option>');
            //     }
            // });

            const selectedJobTitleId = "{{ $personnel->job_title }}";

            function loadJobTitles() {
                let deptId = $('select[name="departementid"]').val();
                let jobType = $('#job_type').val();
                let $jobTitle = $('#job_title');
                console.log("Selected Job Title ID:", selectedJobTitleId);
                $jobTitle.empty().append('<option value="">Loading...</option>');

                if (!deptId || !jobType) {
                    $jobTitle.html('<option value="">Select</option>');
                    return;
                }

                let url =
                    jobType === 'New' ?
                    `/api/vacant-employees/${deptId}` :
                    `/api/replacement-employees/${deptId}`;

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
                                const isSelected = emp.departement_name == selectedJobTitleId ?
                                    'selected' : '';
                                console.log("Adding job title option:", emp.departement_name,
                                    emp.subgrade_name, isSelected);
                                $jobTitle.append(`
                                    <option value="${emp.departement_id}" 
                                            data-title-level="${emp.subgrade_name}" 
                                            data-parent-id="${emp.parent_id}"        
                                            data-subgrade-id="${subgradeId}"> 
                                            ${isSelected}>
                                        ${emp.departement_name}-${emp.subgrade_name}
                                    </option>`);
                            });

                            // Jika ditemukan dan dipilih otomatis, trigger change agar data lain ikut terisi
                            $jobTitle.trigger('change');
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
            $('select[name="departementid"], #job_type').on('change', function() {
                loadJobTitles();
            });


            $('#job_title').on('change', function() {
                let selected = $(this).find(':selected');
                let titleLevel = selected.data('title-level') || '';
                let parentId = selected.data('parent-id') || '';
                let deptId = $('select[name="departementid"]').val();
                let docid = "{{ $personnel->docid }}";

                $('#job_level').val(titleLevel).prop('readonly', true); // isi title level

                // SET subgrade_id
                const subgradeId = selected.data('subgrade-id') || '';
                $('#subgrade_id').val(subgradeId);

                if (parentId) {
                    $.ajax({
                        url: `/api/job-parent-info-edit/${parentId}/${selected.val()}/${deptId}?docid=${docid}`,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {

                            console.log("Skill:", data.skill);
                            console.log("Tags:", data.tags);

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

                            // ✅ Tampilkan skill ke qualificationTable
                            let $skillTable = $('#qualificationTable');
                            $skillTable.empty(); // kosongkan isi sebelumnya

                            if (data.skill && data.skill.length > 0) {
                                $.each(data.skill, function(index, skill) {
                                    $skillTable.append(`
                                        <tr class="qualification-row">
                                            <td class="border border-gray-200 p-3 text-center dark:border-gray-700">${index + 1}</td>
                                            <td class="border border-gray-200 p-3 dark:border-gray-700">
                                                <input type="text" name="qualification[]" value="${skill.job_qualification_descr}" class="w-full border-none bg-transparent p-1 focus:outline-none focus:ring-0">
                                            </td>
                                            <td class="border border-gray-200 p-3 text-center dark:border-gray-700">
                                                <button type="button"
                                                    class="removeQualification bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded">
                                                    🗑️
                                                </button>
                                                
                                            </td>
                                        </tr>
                                    `);
                                });
                            } else {
                                $skillTable.append(
                                    '<tr><td colspan="3" class="text-center p-2 border">No skill found</td></tr>'
                                );
                            }


                            // ✅ Tampilkan tags ke input tags (misal input hidden atau tagify)
                            let $tagsInput = $('#tags');
                            $tagsInput.empty(); // kosongkan opsi lama

                            if (data.tags && data.tags.length > 0) {
                                $.each(data.tags, function(i, tag) {
                                    $tagsInput.append(
                                        `<option selected value="${tag.job_tags}">${tag.job_tags}</option>`
                                    );
                                });
                                $tagsInput.trigger('change'); // jika pakai Select2
                            }


                        },
                        error: function() {
                            $('#immediate_superior').val('');
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



</x-app-layout>
