<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-4">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-4 px-6 py-4 lg:px-8">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden rounded-lg bg-white sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col dark:bg-gray-800">
                        <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div class="flex w-full flex-col pl-8 pr-8 pt-8">
                                <div class="flex justify-between dark:border-gray-600">
                                    <h2 class="mb-4 text-base font-bold">Edit Personnel Requisition</h2>
                                    <h2 class="mb-4 text-base font-bold">{{ $personnel->docid }}</h2>
                                </div>
                                <div
                                    class="mt-4 grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <input type="hidden" name="_method" value="PUT">
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-semibold text-gray-700 dark:text-gray-300">Company</label>
                                        <select name="cpnyid"
                                            class="select2 flex-1 rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            @foreach ($usercpny as $p)
                                                <option value="{{ $p->cpnyid }}"
                                                    {{ $p->cpnyid == $personnel->cpnyid ? 'selected' : '' }}>
                                                    {{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label
                                            class="block font-semibold text-gray-700 dark:text-gray-300">Department</label>
                                        <select name="departementid"
                                            class="select2 flex-1 rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            @foreach ($userdept as $p)
                                                <option value="{{ $p->deptname }}"
                                                    {{ $p->deptname == $personnel->departementid ? 'selected' : '' }}>
                                                    {{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Job Title</label>
                                        <input type="text" name="job_title"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            value="{{ $personnel->job_title }}">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Job Level</label>
                                        <select name="job_level"
                                            class="select2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            @foreach ($joblevel as $p)
                                                <option value="{{ $p->title_level }}"
                                                    {{ $personnel->job_level == $p->title_level ? 'selected' : '' }}>
                                                    {{ $p->title_level }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300">Immediate
                                            Superior</label>
                                        <input type="text" name="immediate_superior" id="immediate_superior"
                                            value={{ $personnel->immediate_superior }}
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="block font-semibold text-gray-700 dark:text-gray-300">State
                                            Position</label>
                                        <input type="text" name="state_position" id="state_position"
                                            value={{ $personnel->state_position }}
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-semibold text-gray-700 dark:text-gray-300">Job Type</label>
                                        <select name="job_type" id="job_type"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            <option value="" {{ $personnel->job_type == '' ? 'selected' : '' }}>
                                            </option>
                                            <option value="Replacement"
                                                {{ $personnel->job_type == 'Replacement' ? 'selected' : '' }}>
                                                Replacement</option>
                                            <option value="Temporary"
                                                {{ $personnel->job_type == 'Temporary' ? 'selected' : '' }}>Temporary
                                            </option>
                                        </select>
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Reason for
                                            Vacancy</label>
                                        <textarea name="reason_vacancy"
                                            id="reason_vacancy"class="w-full h-13 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800">{{ $personnel->reason_vacancy }}</textarea>
                                    </div>
                                </div>
                                <div
                                    class="mb-6 mt-6 grid grid-cols-1 gap-4 rounded-l bg-gray-200/20 p-6 sm:grid-cols-3">
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="w-80 font-medium text-gray-700 dark:text-gray-300">Total Number
                                            Required</label>
                                        <input type="number" name="required" id="required"
                                            value="{{ $personnel->required }}"
                                            class="number-only w-50 w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="number" name="actual" id="actual"
                                            value="{{ $personnel->actual }}"
                                            class="number-only w-50 w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label class="W-100 font-medium text-gray-700 dark:text-gray-300">The Actual
                                            Number</label>
                                        <input type="number" name="total_actual" id="total_actual"
                                            value="{{ $personnel->total_actual }}"
                                            class="number-only w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                </div>
                                <div class="border-b"></div>
                            </div>
                            <!-- Job Responsibilities (Editable) -->
                            <div class="flex w-full flex-col gap-2 rounded-xl pl-8 pr-8 pt-4">
                                <div class="flex w-full flex-col">
                                    <details class="group" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Job Responsibilities</span>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
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
                            <!-- Job Qualification (Editable) -->
                            <div class="flex w-full flex-col gap-2 rounded-xl pl-8 pr-8 pt-4">
                                <div class="flex w-full flex-col">
                                    <details class="group" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Job Qualification</span>
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
                                                    <label
                                                        class="font-medium text-gray-700 dark:text-gray-300">Start</label>
                                                    <input type="number" name="experience_start"
                                                        id="experience_start"
                                                        value="{{ $personnel->experience_start }}" min="0"
                                                        placeholder="Input here"
                                                        class="mt-2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                                </div>
                                                <div class="w-1/2">
                                                    <label
                                                        class="font-medium text-gray-700 dark:text-gray-300">End</label>
                                                    <input type="number" name="experience_end" id="experience_end"
                                                        value="{{ $personnel->experience_end }}" min="0"
                                                        placeholder="Input here"
                                                        class="mt-2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                                </div>
                                            </div>
                                            <div class="border-b"></div>
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
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg> Add Column
                                            </button>
                                        </div>

                                        {{-- <div class="h-auto flex flex-col justify-start">
                                            <button type="button" id="addQualification" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg> Add Column
                                            </button>
                                            <div class="overflow-y-auto"> 
                                                <table class="w-full mt-3 mb-10">
                                                    <thead class="bg-gray-100/10">
                                                        <tr>
                                                            <th class="p-3  border text-center w-12">No</th>
                                                            <th class="p-3 border">Qualification</th>
                                                            <th class="p-3 border text-center w-16">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="qualificationTable">
                                                         @foreach ($jobqua as $key => $resp)
                                                            <tr>
                                                                <td class="p-3 border text-center">{{ $key+1 }}</td>
                                                                <td class="p-3 border">
                                                                    <input type="text" name="qualification[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" value="{{ $resp->job_qualification_descr }}">
                                                                </td>
                                                                <td class="p-3 border text-center">
                                                                    <button type="button" class="removeQualification  bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded">🗑️
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div> --}}
                                    </details>
                                </div>
                                <div class="border-b"></div>
                            </div>
                            <div class="flex w-full flex-col gap-2 rounded-xl pl-8 pr-8 pt-4">
                                <div class="flex w-full flex-col">
                                    <details class="group mb-4" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex h-auto flex-col justify-start">
                                            <div id="attachmentsContainer">
                                                @foreach ($attachment as $attach)
                                                    <div class="attachment-row flex items-center gap-2"
                                                        data-attachid="{{ $attach->id }}">
                                                        <a href="{{ url('/attachments/' . $attach->attachfile) }}"
                                                            target="_blank" class="mt-4 w-full border p-3 text-sm">📎
                                                            {{ $attach->name }}</a>
                                                        <button type="button"
                                                            class="removeAttachment2 mt-4 rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30 dark:bg-red-700/30"
                                                            data-id="{{ $attach->id }}">🗑️
                                                        </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="addAttachment"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-indigo-700 hover:bg-indigo-200/10 hover:font-medium hover:text-indigo-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg> Add Attachment
                                            </button>
                                        </div>
                                    </details>
                                </div>
                                <div class="border-b"></div>
                            </div>
                            <div class="flex w-full flex-col">
                                <div class="flex w-1/2 w-full flex-row justify-end gap-4 border-b pb-4 pl-8 pr-8">
                                    <div class="w-1/8 flex flex-col justify-end">
                                        <button type="submit" id="#"
                                            class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-600 bg-red-600 p-2 text-white hover:border-red-600 hover:bg-red-600/10 hover:font-medium hover:text-red-600">
                                            <span id="btnText">Cancel</span>
                                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="w-1/8 flex flex-col justify-end">
                                        <button type="submit" id="submitBtn"
                                            class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-500 bg-blue-500 p-2 text-white hover:border-blue-500 hover:bg-blue-500/10 hover:font-medium hover:text-blue-500">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#personnelForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let url = "{{ route('personnels.update', $personnel->id) }}";

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        //alert("Personnel Requisition Updated Successfully!");
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Personnel Requisition Updated Successfully!");
                        window.location.href = "/personnels";
                    },
                    error: function(xhr) {
                        alert("Error! Please check the input.");
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden');
                    }
                });
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


        // Add Attachment
        $('#addAttachment').click(function() {
            $('#attachmentsContainer').append(`
                <div class="attachment-row flex items-center gap-2">
                    <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded">
                    <button type="button" class="removeAttachment mt-4 bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded">🗑️</button>
                </div>
            `);
        });

        // Remove Attachment
        $(document).on('click', '.removeAttachment', function() {
            $(this).closest('.attachment-row').remove();
        });

        $(document).on('click', '.removeAttachment2', function() {
            let attachmentId = $(this).data('id'); // Ambil ID attachment
            let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

            // Cek konfirmasi pengguna
            let confirmDelete = confirm('Are you sure you want to remove this attachment?');

            if (confirmDelete) {
                $.ajax({
                    url: "/personnels/remove-attachment/" + attachmentId, // Endpoint ke controller
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
    </script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


</x-app-layout>
