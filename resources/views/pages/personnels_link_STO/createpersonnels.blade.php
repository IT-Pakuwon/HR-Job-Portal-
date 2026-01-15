<x-app-layout>
    <style>
        /* tinggi ~42px seperti p-2.5 Tailwind */
        .select2-container .select2-selection--single {
            height: 42px;
            border-radius: 0.5rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 42px;
            padding-left: .75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px;
            right: .5rem;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-base font-extrabold text-gray-800 dark:text-white">Create Personnel
                                Requisition
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    {{-- @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach --}}
                                    @foreach ($companies as $p)
                                        <option value="{{ $p->cpnyid }}">{{ $p->cpnyid }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Division</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="division" required>
                                    <option value="" disabled selected>Select Division</option>
                                    @foreach ($division as $p)
                                        <option value="{{ $p->division_id }}">{{ $p->division_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="departementid" required>
                                    {{-- @foreach ($userdept as $p)
                                        <option value="{{ $p->deptname }}"
                                            {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>
                                            {{ $p->deptname }}</option>
                                    @endforeach --}}
                                    @foreach ($departements as $p)
                                        <option value="{{ $p->deptname }}">
                                            {{ $p->deptname }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Placement
                                    Location</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="siteid" id="siteid" required>
                                    <option value="">-- Select Site --</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Detail Info</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="pt-6">
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Job
                                            Type</label>
                                        <select name="job_type" id="job_type"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="" disabled>Select Job Type</option>
                                            <option value="New">New</option>
                                            <option value="Replacement">Replacement</option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Job
                                            Title</label>
                                        <select name="job_title" id="job_title"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required>
                                            <option value="">Select/option>
                                        </select>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Job
                                            Level</label>
                                        <input type="hidden" name="subgrade_id" id="subgrade_id">
                                        <input type="text" name="job_level" id="job_level"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">Immediate
                                            Superior</label>
                                        <input type="text" name="immediate_superior" id="immediate_superior"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">State
                                            Position</label>
                                        <input type="text" name="state_position" id="state_position"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Reason
                                            for
                                            Vacancy</label>
                                        <textarea name="reason_vacancy" id="reason_vacancy"
                                            class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            required></textarea>
                                    </div>
                                </div>
                                <div
                                    class="mt-8 grid grid-cols-1 gap-6 rounded-lg bg-gray-100/40 p-4 sm:grid-cols-3 dark:bg-gray-700/40">
                                    <div class="flex flex-col gap-2">
                                        <label
                                            class="block text-xs font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="number" name="actual" id="actual" min="0"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Number
                                            Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            class="pointer-events-none w-full rounded-lg border border-gray-300 bg-gray-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                            readonly>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Total
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

                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Responsibilities</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-60 flex-col overflow-y-auto pt-6">
                                <table id="jobProfileTable"
                                    class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-700">
                                            <th
                                                class="w-10 border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                No</th>
                                            <th
                                                class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                Job Purpose</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    </div>

                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Job Qualification</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex flex-col gap-6 pt-6">
                                <div class="flex flex-col gap-2">
                                    <label class="block text-xs font-semibold text-gray-800 dark:text-gray-200">🔹
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
                                    <label class="block text-xs font-semibold text-gray-800 dark:text-gray-200">🔹
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
                                    <label class="block text-xs font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Tags</label>
                                    <select name="tags[]" id="tags" multiple
                                        class="tags-input w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    </select>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <label class="block text-xs font-semibold text-gray-800 dark:text-gray-200">🔹
                                        Skill</label>
                                    <div class="max-h-60 overflow-y-auto">
                                        <table
                                            class="w-full border-collapse border border-gray-200 dark:border-gray-700">
                                            <thead>
                                                <tr class="bg-gray-50 dark:bg-gray-700">
                                                    <th
                                                        class="w-10 border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                        No</th>
                                                    <th
                                                        class="border border-gray-200 p-3 text-left text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                                                        Skill</th>
                                                    <th
                                                        class="w-16 border border-gray-200 p-3 text-center text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
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
                                                            class="removeQualification rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">
                                                            🗑️
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" id="addQualification"
                                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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

                    <div class="w-full rounded-xl bg-white p-4 shadow-md dark:bg-gray-800">
                        <details class="group" open>
                            <summary
                                class="flex cursor-pointer items-center justify-between border-b border-gray-200 pb-4 text-base font-extrabold text-gray-800 dark:border-gray-700 dark:text-white">
                                <span>Attachments</span>
                                <span class="text-xs font-medium text-gray-500 transition-all group-open:hidden">See
                                    details &rarr;</span>
                                <span
                                    class="hidden text-xs font-medium text-gray-500 transition-all group-open:inline">Hide
                                    details &darr;</span>
                            </summary>
                            <div class="flex max-h-[125px] flex-col overflow-y-auto pt-6">
                                <div id="attachmentsContainer">
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addAttachment"
                                class="mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                    class="flex items-center gap-2 rounded-md bg-red-500 px-4 py-2 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
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
                Personnel Requisition Created Successfully!
            </div>
        </div>
    </div>

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
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

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
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-xs text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
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
            let qualificationCount = 1;

            // Fungsi untuk Menambah Baris Qualification
            $('#addQualification').click(function() {
                qualificationCount++;
                $('#qualificationTable').append(`
                <tr class="qualification-row">
                    <td class="p-3 border text-center">${qualificationCount}</td>
                    <td class="p-3 border">
                        <input type="text" name="qualification[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeQualification bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
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
                $('#qualificationTable tr').each(function() {
                    qualificationCount++;
                    $(this).find('td:first').text(qualificationCount);
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
                            $siteSelect.append('<option value="">-- Select Site --</option>');

                            $.each(data, function(key, value) {
                                $siteSelect.append(
                                    `<option value="${value.site}">${value.site}</option>`
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
            $('select[name="departementid"], #job_type').on('change', function() {
                loadJobTitles();
            });


            $('#job_title').on('change', function() {
                let selected = $(this).find(':selected');
                let titleLevel = selected.data('title-level') || '';
                let parentId = selected.data('parent-id') || '';
                let deptId = $('select[name="departementid"]').val();

                $('#job_level').val(titleLevel).prop('readonly', true); // isi title level

                // SET subgrade_id
                const subgradeId = selected.data('subgrade-id') || '';
                $('#subgrade_id').val(subgradeId);

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
    {{-- <script>
        $(function () {
            const $cpny = $('select[name="cpnyid"]');
            const $dept = $('select[name="departementid"]');

            // Jadikan searchable
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

            // Catatan: event .on('change') yang sudah Anda tulis tetap bekerja dengan Select2.
            // Jika dropdown berada di dalam modal/elemen ber-z-index tinggi, set:
            // dropdownParent: $('#id-modal-anda')
        });
    </script> --}}
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
        });
    </script>





</x-app-layout>
