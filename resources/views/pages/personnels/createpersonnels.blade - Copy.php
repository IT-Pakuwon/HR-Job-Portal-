<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div
                                class="flex w-full flex-col rounded-xl border-b bg-white p-4 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="text-base font-bold">Create Personnel Requisition</h2>
                                </div>
                                <div
                                    class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Company</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="cpnyid" required>
                                            @foreach ($usercpny as $p)
                                                <option value="{{ $p->cpnyid }}"
                                                    {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                                    {{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Divisi</label>
                                        <input type="text" name="job_title" id="job_title"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Department</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="departementid" required>
                                            @foreach ($userdept as $p)
                                                <option value="{{ $p->deptname }}"
                                                    {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>
                                                    {{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Lokasi
                                            Kerja</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="job_level" required>
                                            @foreach ($joblevel as $p)
                                                <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Job Responsibilities -->
                            <div
                                class="flex w-full w-full flex-col rounded-xl border-b bg-white p-6 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-base font-bold">Job Detail Info</h2>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Job
                                            Title</label>
                                        <input type="text" name="job_title" id="job_title"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Job
                                            Level</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="job_level" required>
                                            @foreach ($joblevel as $p)
                                                <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Immediate
                                            Superior</label>
                                        <input type="text" name="immediate_superior" id="immediate_superior"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">State
                                            Position</label>
                                        <input type="text" name="state_position" id="state_position"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Job
                                            Type</label>
                                        <select name="job_type" id="job_type"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                            <option value="Replacement">Replacement</option>
                                            <option value="Temporary">Temporary</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Reason
                                            for Vacancy</label>
                                        <textarea name="reason_vacancy" id="reason_vacancy"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required></textarea>
                                    </div>
                                </div>
                                <div
                                    class="mb-6 mt-6 grid grid-cols-1 gap-4 rounded-l bg-gray-200/40 p-6 sm:grid-cols-3">
                                    <div class="flex items-center gap-4">
                                        <label class="font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="number" name="actual" id="actual" min="0"
                                            class="number-only w-50 w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Number
                                            Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            class="number-only w-50 w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Total
                                            Actual Number</label>
                                        <input type="number" name="total_actual" id="total_actual" min="0"
                                            class="number-only w-full rounded-sm border border-gray-300/50 bg-white p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
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
                                                        <tr class="responsibilities-row">
                                                            <td class="border p-3 text-center">1</td>
                                                            <td class="border p-3">
                                                                <input type="text" name="responsibilities[]"
                                                                    placeholder="Type here..."
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0">
                                                            </td>
                                                            <td class="border-b border-r border-t p-3 text-center">
                                                                <button type="button"
                                                                    class="removeResponsibilities hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                            </td>
                                                        </tr>
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
                            </div>
                            <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                                <div class="flex w-full flex-col rounded-xl p-4">
                                    <details class="group" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Job Qualification</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex flex-row gap-2 pb-4 pt-4">
                                            <div class="flex w-1/2 flex-col">
                                                <label class="mb-2 font-semibold"> 🔹 Education</label>
                                                <div class="relative pl-4">
                                                    <select name="education" id="education"
                                                        class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
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
                                            </div>
                                            <div class="flex w-1/2 flex-col">
                                                <label class="mb-2 font-semibold">🔹 Tags</label>
                                                <div class="relative pl-4">
                                                    <select name="tags[]" id="tags" multiple
                                                        class="tags-input w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Experience -->
                                        <div class="flex flex-col gap-2 pb-4 pt-4">
                                            <label class="mb-2 font-semibold"> 🔹 Experience</label>
                                            <div class="flex gap-4 pl-4">
                                                <div class="flex w-1/2 flex-col">
                                                    <label
                                                        class="mb-2 font-medium text-gray-700 dark:text-gray-300">Start</label>
                                                    <input type="number" name="experience_start"
                                                        id="experience_start" min="0" placeholder="Input here"
                                                        class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                </div>
                                                <div class="flex w-1/2 flex-col">
                                                    <label
                                                        class="mb-2 font-medium text-gray-700 dark:text-gray-300">End</label>
                                                    <input type="number" name="experience_end" id="experience_end"
                                                        min="0" placeholder="Input here"
                                                        class="w-full rounded-lg border border-gray-300 bg-white p-3 text-gray-700 shadow-sm focus:ring-2 focus:ring-blue-400 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200">
                                                </div>
                                            </div>
                                        </div>
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
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button type="button" id="addQualification"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z"
                                                        clip-rule="evenodd" />
                                                </svg> Add Column
                                            </button>
                                        </div>
                                    </details>
                                </div>
                            </div>
                            <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                                <div class="flex w-1/2 w-full flex-col border-b p-4">
                                    <details class="group mb-4" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex h-auto flex-col justify-start">
                                            <div id="attachmentsContainer">
                                                <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]"
                                                        class="mt-4 w-full border p-3 text-sm">
                                                    <button type="button"
                                                        class="removeAttachment mt-4 hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" id="addAttachment"
                                                class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-gray-700 bg-gray-200/10 p-2 text-gray-800 hover:border-red-700 hover:bg-red-200/10 hover:font-medium hover:text-red-800">
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
                                <div class="flex h-auto w-full flex-row justify-end gap-4 pl-4 pr-4">
                                    <div class="w-1/8 flex flex-col justify-start">
                                        <button id="cancelBtn"
                                            class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
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
                                    <div class="w-1/8 flex flex-col justify-start" ">
                                        <button type="submit" id="submitBtn" class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-blue-700 bg-blue-200/10 p-2 text-blue-700 hover:border-blue-700 hover:bg-blue-700 hover:font-medium hover:text-white">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r=" 10"
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
                </div>
                <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                    Personnel Requisition Created Successfully!
                </div>
            </div>
        </div>
    </div>

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
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded mt-4">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
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

</x-app-layout>
