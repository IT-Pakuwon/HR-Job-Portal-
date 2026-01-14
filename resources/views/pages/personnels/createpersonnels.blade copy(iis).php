<x-app-layout>
    <div class="py-1 w-full max-w-9xl mx-auto">
        <div class="grid">
            <div class="px-0 px-1 lg:px-8 py-4 w-full max-w-9xl mx-auto">  
                <div class="gap-6">  
                    <div class="flex flex-col xl:flex-col sm:col-span-1 lg:row-span-2 xl:col-span-1 gap-10 overflow-hidden">
                        <form id="personnelForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div class="flex flex-col w-full  rounded-xl shadow-sm border-b bg-white dark:bg-gray-800 w-full p-6">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="text-base font-bold mb-2">Create Personnel Requisition</h2>
                                </div>
                                <div class="grid grid-cols-1 mt-2 md:grid-cols-2 mt-2 lg:grid-cols-2 gap-6 dark:border-gray-600 ">
                                    <div class="flex items-center gap-2">
                                        <label class="text-gray-700 dark:text-gray-300 font-medium w-35">Company</label>
                                        <select class="flex-1 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800"  name="cpnyid" required>
                                            @foreach($usercpny as $p)
                                                <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>                    
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-33">Department</label>
                                        <select class="flex-1 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800"   name="departementid" required>
                                            @foreach($userdept as $p)
                                                <option value="{{ $p->deptname }}" {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>{{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1  w-40">Job Title</label>
                                        <input type="text" name="job_title" id="job_title"  class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1  w-40">Job Level</label>
                                        <select class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800"   name="job_level" required>
                                            @foreach($joblevel as $p)
                                                <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                            @endforeach
                                        </select>
                                    </div>                    
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1  w-40">Immediate Superior</label>
                                        <input type="text" name="immediate_superior" id="immediate_superior" class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1  w-40">State Position</label>
                                        <input type="text" name="state_position" id="state_position" class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40" >Job Type</label>
                                        <select name="job_type" id="job_type" class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                                            <option value="Replacement">Replacement</option>
                                            <option value="Temporary">Temporary</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">Reason for Vacancy</label>
                                        <textarea name="reason_vacancy" id="reason_vacancy"  class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required></textarea>
                                    </div>  
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 mb-6 mt-6 gap-4 bg-gray-200/40 p-6 rounded-l">
                                    <div class="flex items-center gap-4">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">Total Number Required</label>
                                        <input type="number" name="required" id="required" min="0"
                                            class="number-only  w-full p-3 border border-gray-300/50 rounded-sm focus:ring focus:ring-blue-300 bg-white dark:bg-gray-800 w-50" required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="text-gray-700 dark:text-gray-300 font-medium">Actual</label>
                                        <input type="number" name="actual" id="actual" min="0"
                                            class="number-only w-full p-3 border border-gray-300/50 rounded-sm focus:ring focus:ring-blue-300 bg-white dark:bg-gray-800 w-50" required>
                                    </div>
                                    <div class="flex items-center gap-4 mb-8">
                                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">The Actual Number</label>
                                        <input type="number" name="total_actual" id="total_actual" min="0"
                                            class="number-only w-full p-3 border border-gray-300/50 rounded-sm focus:ring focus:ring-blue-300 bg-white dark:bg-gray-800" required>
                                    </div>
                                </div>
                            </div>
                            <!-- Job Responsibilities -->
                            <div class="flex flex-col w-full rounded-xl border-b gap-2 bg-white dark:bg-gray-800 ">
                                <div class="flex flex-col w-full rounded-xl p-4">
                                    <details class="group" open>
                                        <summary class="flex items-center justify-between cursor-pointer mb-4 rounded">
                                            <span class="text-sm font-semibold">Job Responsibilities</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="h-auto flex flex-col justify-start">
                                            <button type="button" id="addResponsibilities"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Add Column
                                            </button>
                                            <div class="overflow-y-auto"> 
                                                <table class="w-full mt-3 mb-4">
                                                    <thead class="bg-gray-100/10">
                                                        <tr>
                                                            <th class="p-3 border text-center w-12">No</th>
                                                            <th class="p-3 border">Responsibility</th>
                                                            <th class="p-3 border text-center w-16">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="responsibilitiesTable">
                                                        <tr class="responsibilities-row">
                                                            <td class="p-3  border text-center">1</td>
                                                            <td class="p-3 border ">
                                                                <input type="text" name="responsibilities[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                                                            </td>
                                                            <td class="p-3 border text-center">
                                                                <button type="button" class="removeResponsibilities bg-red-200/10  hover:border-red-700  hover:bg-red-400/30  border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </details>
                                </div>
                            </div>
                                <!-- Job Qualification -->
                            <div class="flex flex-col w-full rounded-xl border-b gap-2 bg-white dark:bg-gray-800 ">
                                <div class="flex flex-col w-full gap-4 p-4">
                                    <details class="group" open>
                                        <summary class="flex items-center justify-between cursor-pointer mb-4 rounded">
                                            <span class="text-sm font-semibold">Job Qualification</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                            <!-- Education -->
                                            <div class="flex flex-col gap-2">
                                                <label class="font-semibold mb-2"> 🔹 Education</label>
                                                <div class="relative  pl-4">
                                                    <select name="education" id="education" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-400 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm">
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
                            
                                            <!-- Experience -->
                                            <div class="flex flex-col  gap-2 pt-4 pb-4">
                                                <label class="font-semibold mb-2"> 🔹 Experience</label>
                                                <div class="flex gap-4  pl-4">
                                                    <div class="w-1/2 flex flex-col">
                                                        <label class="text-gray-700 dark:text-gray-300 font-medium mb-2">Start</label>
                                                        <input type="number" name="experience_start" id="experience_start" min="0" placeholder="Input here"
                                                            class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-400 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm">
                                                    </div>
                                                    <div class="w-1/2 flex flex-col">
                                                        <label class="text-gray-700 dark:text-gray-300 font-medium mb-2">End</label>
                                                        <input type="number" name="experience_end" id="experience_end" min="0" placeholder="Input here"
                                                            class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-400 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 shadow-sm">
                                                    </div>
                                                </div>
                                            </div>
                                        <div class="h-auto flex flex-col justify-start">
                                            <label class="font-semibold mb-2"> 🔹 Skill</label>
                                            <div class="overflow-y-auto"> 
                                                <table class="w-full mt-3 mb-4">
                                                    <thead class="bg-gray-100/10">
                                                        <tr>
                                                            <th class="p-3  border text-center w-12">No</th>
                                                            <th class="p-3 border">Skill</th>
                                                            <th class="p-3 border text-center w-16">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="qualificationTable">
                                                        <tr class="qualification-row">
                                                            <td class="p-3 border text-center">1</td>
                                                            <td class="p-3 border">
                                                                <input type="text" name="qualification[]" placeholder="Type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                                                            </td>
                                                            <td class="p-3 border text-center">
                                                                <button type="button" class="removeQualification  bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button type="button" id="addQualification" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg> Add Column
                                            </button>
                                        </div>
                                    </details>
                                </div>
                            </div>
                            <div class="flex flex-col w-full rounded-xl border-b gap-2 bg-white dark:bg-gray-800 ">
                                <div class="flex flex-col w-1/2 p-4 border-b w-full">
                                    <details class="group mb-4" open>
                                        <summary class="flex items-center justify-between cursor-pointer mb-4 rounded">
                                            <span class="text-sm font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="h-auto flex flex-col justify-start">
                                            <button type="button" id="addAttachment" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg> Add Attachment
                                            </button>
                                            <div id="attachmentsContainer">
                                                <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]" class="w-full p-3 mt-4 text-sm border">
                                                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">
                                                        🗑️
                                                    </button>
                                                </div>
                                            </div>
                                        </div>   
                                    </details>          
                                </div>
                                <div class="h-auto w-full flex flex-row justify-end pl-4 pr-4 gap-4">
                                    <div class="w-1/8  flex flex-col justify-start" ">
                                        <button type="submit" id="submitBtn" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-blue-700 border-blue-700 hover:bg-blue-700 hover:text-white hover:border-blue-700 bg-blue-200/10 p-2 ">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                            </svg>
                                        </button> 
                                    </div>
                                    <div class="w-1/8  flex flex-col justify-start">
                                        <button type="submit" id="#" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-red-700 border-red-700 hover:bg-red-700 hover:text-white hover:border-red-700 bg-red-200/10 p-2 ">
                                            <span id="btnText">Cancel Approval</span>
                                            <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                            </svg>
                                        </button>   
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div id="successMessage" class="mt-4 hidden text-green-600 font-bold">
                    Personnel Requisition Created Successfully!
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        $('#personnelForm').submit(function (e) {
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
                success: function (response) {
                    $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                    $('#personnelForm')[0].reset(); // Reset form setelah submit

                    // Reset Tombol ke Semula
                    $('#submitBtn').attr('disabled', false);
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                    window.location.href = "/personnels";
                },
                error: function (xhr) {
                    alert('Error! Please check the input.');

                    // Reset Tombol ke Semula
                    $('#submitBtn').attr('disabled', false);
                    $('#btnText').text('Submit Approval');
                    $('#loadingSpinner').addClass('hidden');
                }
            });
        });
    });


</script>    

<script>
    $(document).ready(function () {
    // Fungsi Tambah Attachment
    $('#addAttachment').click(function () {
        $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded mt-4">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
            </div>
        `);
        toggleDeleteButton();
    });

    // Fungsi Hapus Attachment
    $(document).on('click', '.removeAttachment', function () {
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
    $(document).ready(function () {
        let responsibilityCount = 1;

        // Fungsi untuk Menambah Baris Responsibility
        $('#addResponsibilities').click(function () {
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
        $(document).on('click', '.removeResponsibilities', function () {
            $(this).closest('.responsibilities-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            responsibilityCount = 0;
            $('#responsibilitiesTable tr').each(function () {
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
    $(document).ready(function () {
        let qualificationCount = 1;

        // Fungsi untuk Menambah Baris Qualification
        $('#addQualification').click(function () {
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
        $(document).on('click', '.removeQualification', function () {
            $(this).closest('.qualification-row').remove();
            updateRowNumbers();
            updateRemoveButtons();
        });

        // Fungsi untuk Memperbarui Nomor pada Tabel
        function updateRowNumbers() {
            qualificationCount = 0;
            $('#qualificationTable tr').each(function () {
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
    $(document).ready(function () {
        // Cegah input selain angka saat mengetik
        $('.number-only').on('keypress', function (event) {
            let charCode = event.which ? event.which : event.keyCode;
            if (charCode < 48 || charCode > 57) {
                event.preventDefault();
            }
        });

        // Hapus karakter selain angka jika sudah terlanjur masuk
        $('.number-only').on('input', function () {
            let value = $(this).val();
            $(this).val(value.replace(/[^0-9]/g, ''));
        });
    });
</script>

</x-app-layout>
