<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
        <div class="flex items-center justify-end mb-4 sm:mb-0"></div>
        <div class="mt-2 mb-2 bg-white  dark:bg-gray-800 p-6 rounded-xl">
            <div class="w-full">

            <div class="border-b mb-8">
                <h2 class="text-2xl font-bold mb-4 bg-white ">Create Personnel Requisition</h2>
            </div>
            <form id="personnelForm" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-10 border-b">
                    <div class="flex items-center gap-4">
                        <label class="text-gray-700 dark:text-gray-300 font-medium w-29">Company</label>
                        <select class="flex-1 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" name="cpnyid" required>
                            @foreach($usercpny as $p)
                                <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                            @endforeach
                        </select>
                    </div>                    
                    <div class="flex items-center gap-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">Department</label>
                        <select class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" name="departementid" required>
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
                    <div class="flex items-center gap-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">Total Number Required</label>
                        <input type="number" name="required" id="required" min="0"
                            class="number-only w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">Actual</label>
                        <input type="number" name="actual" id="actual" min="0"
                            class="number-only w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                    </div>
                    <div class="flex items-center gap-4 mb-8">
                        <label class="block text-gray-700 dark:text-gray-300 font-medium mb-1 w-40">The Actual Number</label>
                        <input type="number" name="total_actual" id="total_actual" min="0"
                            class="number-only w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800" required>
                    </div>
                </div>

            </div>
            <!-- Job Responsibilities -->
            <div class="mt-6 border-b">
                <div class="flex items-center justify-between">
                    <label class="block text-lg font-semibold">Job Responsibilities</label>
                    <button type="button" id="addResponsibilities" class="px-4 py-2 text-gray-700 rounded hover:border-red-800 hover: flex items-center gap-2">
                        + Add
                    </button>
                </div>
                <table class="w-full mt-3 mb-10">
                    <thead class="bg-gray-100/10">
                        <tr>
                            <th class="p-3  border text-center w-12">No</th>
                            <th class="p-3 border ">Responsibility</th>
                            <th class="p-3  border text-center w-16">Action</th>
                        </tr>
                    </thead>
                    <tbody id="responsibilitiesTable">
                        <tr class="responsibilities-row">
                            <td class="p-3  border text-center">1</td>
                            <td class="p-3 border ">
                                <input type="text" name="responsibilities[]" placeholder="type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                            </td>
                            <td class="p-3 border text-center">
                                <button type="button" class="removeResponsibilities bg-red-200/10 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

                <!-- Job Qualification -->
                <div class="mt-6 border-b">
                    <div class="flex items-center justify-between">
                        <label class="block text-lg font-semibold">Job Qualification</label>
                            <button type="button" id="addQualification" class="px-4 py-2 text-gray-700 rounded hover:border-red-800 hover: flex items-center gap-2
                            ">+ Add</button>
                    </div>
                    <table class="w-full mt-3 mb-10">
                        <thead class="bg-gray-100/10">
                            <tr>
                                <th class="p-3  border text-center w-12">No</th>
                                <th class="p-3 border">Qualification</th>
                                <th class="p-3 border text-center w-16">Action</th>
                            </tr>
                        </thead>
                        <tbody id="qualificationTable">
                            <tr class="qualification-row">
                                <td class="p-3 border text-center">1</td>
                                <td class="p-3 border">
                                    <input type="text" name="qualification[]" placeholder="type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                                </td>
                                <td class="p-3 border text-center">
                                    <button type="button" class="removeQualification  bg-red-200/10 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Attachments -->
                <div class="mt-6">
                    <label class="block text-lg font-semibold">Attachments</label>
                    <div id="attachmentsContainer">
                        <div class="attachment-row flex items-center gap-2">
                            <input type="file" name="attachments[]" class="w-full p-3 text-lg border rounded-lg">
                            <button type="button" class="removeAttachment bg-red-200/30 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            <button type="button" id="addAttachment" class="bg-blue-200/30 text-blue-600 p-3 border border-blue-600 hover:bg-blue-600 hover:text-white rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                            </button>
                        </div>
                    </div>                
                </div>

                <div class="mt-6">           
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-500 text-white rounded flex items-center gap-2">
                        <span id="btnText">Submit Approval</span>
                        <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </button>            
                </div>
            </form>

            <div id="successMessage" class="mt-4 hidden text-green-600 font-bold">
                Personnel Requisition Created Successfully!
            </div>
</div>
<div class="sm:flex sm:justify-between sm:items-center mb-8"></div>
<div class="flex items-center justify-end mb-4 sm:mb-0"></div>
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
                <input type="file" name="attachments[]" class="w-full p-3 text-lg border rounded mt-4">
                    <button type="button" class="removeAttachment mt-4 bg-red-200/30 text-red-600 p-3 rounded border border-red-600 hover:text-white hover:bg-red-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                </button>
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
                                <input type="text" name="responsibilities[]" placeholder="type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeResponsibilities bg-red-200/10 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
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

        // updateRemoveButtons();
         // Add Responsibility
        //  $('#addResponsibilities').click(function () {
        //     let rowCount = $('#responsibilitiesTable tr').length + 1;
        //     $('#responsibilitiesTable').append(`
        //         <tr>
        //             <td class="p-3 border text-center">${rowCount}</td>
        //             <td class="p-3 border">
        //                 <input type="text" name="responsibilities[]" class="w-full p-2 border rounded">
        //             </td>
        //             <td class="p-3 border text-center">
        //                 <button type="button" class="removeResponsibilities bg-red-500 text-white px-3 py-1 rounded">X</button>
        //             </td>
        //         </tr>
        //     `);
        // });
    
        // // Remove Responsibility
        // $(document).on('click', '.removeResponsibilities', function () {
        //     $(this).closest('tr').remove();
        //     updateRowNumbers();
        // });
    
        // // Update row numbers after deleting
        // function updateRowNumbers() {
        //     $('#responsibilitiesTable tr').each(function (index) {
        //         $(this).find('td:first').text(index + 1);
        //     });
        // }

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
                        <input type="text" name="qualification[]" placeholder="type here..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent">
                    </td>
                    <td class="p-3 border text-center">
                        <button type="button" class="removeQualification bg-red-200/10 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
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
