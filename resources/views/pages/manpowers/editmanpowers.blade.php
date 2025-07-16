<x-app-layout>
    <div class="py-4 w-full max-w-9xl mx-auto">
         <div class="grid">
            <div class="px-4 px-6 lg:px-8 py-4 w-full max-w-9xl mx-auto">
                <div class="gap-6">  
                    <div class="flex flex-col xl:flex-col sm:col-span-1 lg:row-span-2 xl:col-span-1 gap-10 overflow-hidden  bg-white dark:bg-gray-800 rounded-lg">
                        <form id="manpowerForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div class="flex flex-col w-full pl-8 pt-8 pr-8">
                                <div class="flex justify-between dark:border-gray-600">
                                    <h2 class="text-xl font-bold mb-4">Edit Manpower</h2>
                                    <h2 class="text-xl font-bold mb-4">{{ $manpower->docid }}</h2>
                                </div>
                                <div class="grid grid-cols-1 mt-4 md:grid-cols-2 lg:grid-cols-2 gap-x-4 gap-y-4 dark:border-gray-600">
                                    <input type="hidden" name="_method" value="PUT"> 
                                    <div class="flex flex-col w-full gap-2">
                                        <label class="text-gray-700 dark:text-gray-300 font-semibold">Company</label>
                                        <select name="cpnyid"  class="flex-1 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800 select2">                        
                                            @foreach($usercpny as $p) <option value="{{ $p->cpnyid }}" {{ $p->cpnyid == $manpower->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex flex-col w-full gap-2">
                                        <label class="block text-gray-700 dark:text-gray-300 font-semibold">Department</label>
                                        <select name="departementid" class="flex-1 p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800 select2">                       
                                            @foreach($userdept as $p) <option value="{{ $p->deptname }}" {{ $p->deptname == $manpower->departementid ? 'selected' : '' }}>{{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex flex-col w-full gap-2">
                                        <label class="block text-gray-700 dark:text-gray-300 font-semibold">Periode</label>
                                        <input type="text" name="periodyear" id="periodyear" value={{ $manpower->periodyear }} class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800">
                                    </div>
                                    <div class="flex flex-col w-full gap-2">
                                        <label class="block text-gray-700 dark:text-gray-300 font-semibold">Actual</label>
                                        <input type="text" name="actual" id="actual" value={{ $manpower->actual }} class="w-full p-3 border border-gray-200/50 rounded-sm focus:ring focus:ring-blue-300 bg-gray-200/10 dark:bg-gray-800">
                                    </div>                                      
                                </div> 
                            </div>
                            <!-- Manpower Detail -->
                            <div class="flex flex-col w-full rounded-2xl border-b gap-2 bg-white dark:bg-gray-800 ">
                                <div class="flex flex-col w-full rounded-2xl p-4">
                                    <details class="group" open>
                                        <summary class="flex items-center justify-between cursor-pointer mb-4 rounded">
                                            <span class="text-lg font-semibold">Details</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="h-auto flex flex-col justify-start">                                           
                                            <div class="overflow-y-auto"> 
                                                <table class="w-full mt-3 mb-4">
                                                    <thead class="bg-gray-100/10">
                                                        <tr>
                                                            <th class="p-3 border text-center w-12">No</th>                                                           
                                                            <th class="p-3 border-t border-l">Expected Date</th>
                                                            <th class="p-3 border-t border-l">Job Title</th>
                                                            <th class="p-3 border-t border-l">Job Level</th>
                                                            <th class="p-3 border-t border-l">Qty</th> 
                                                            <th class="p-3 border-t border-l">Justification</th>                                                            
                                                            <th class="p-3 border-t border-r text-center w-16"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="responsibilitiesTable">
                                                        @foreach ($manpowerdetail as $key => $resp)
                                                            <tr class="responsibilities-row">
                                                                <td class="p-3 border text-center">{{ $key + 1 }}</td> 
                                                                <td class="p-3 border">
                                                                    <input type="date" name="expected_employment_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" value="{{ $resp->expected_employment_date }}" required>                                                                
                                                                </td>   
                                                                <td class="p-3 border">
                                                                    <input type="text" name="job_title[]" placeholder="Type Job Title..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" value="{{ $resp->job_title }}" required>
                                                                </td>
                                                                <td class="p-3 border">
                                                                    <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="job_level[]" required>
                                                                        <option value="" disabled {{ $resp->job_level ? '' : 'selected' }}>Select Job Level</option>
                                                                        @foreach($joblevel as $p)                                                                        
                                                                            <option value="{{ $p->title_level }}" {{ $p->title_level == $resp->job_level ? 'selected' : '' }}>
                                                                                {{ $p->title_level }}
                                                                            </option>
                                                                        @endforeach                                                                        
                                                                    </select>
                                                                </td>
                                                                <td class="p-3 border">
                                                                    <input type="number" min="0" name="qty[]" placeholder="Type Qty..." class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" value="{{ $resp->qty }}" required>
                                                                </td>
                                                                <td class="p-3 border">                                                               
                                                                    <textarea name="reason_vacancy[]" placeholder="Type Reason..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>{{ $resp->reason_vacancy }}</textarea>
                                                                </td>                                 
                                                                <td class="p-3 border text-center">
                                                                    <button type="button" class="removeResponsibilities bg-red-200/10 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded hidden">🗑️</button>
                                                                </td>
                                                            </tr>
                                                        @endforeach  
                                                    </tbody>                                                    
                                                </table>
                                            </div>
                                            <button type="button" id="addResponsibilities"  class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-red-800 hover:border-red-700 hover:bg-red-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                </svg>
                                                Add Column
                                            </button>
                                        </div>
                                    </details>
                                </div>
                            </div>                          
                            
                            <div class="flex flex-col w-full rounded-2xl gap-2 pl-8 pt-4 pr-8">
                                <div class="flex flex-col w-full">
                                    <details class="group mb-4" open>
                                        <summary class="flex items-center justify-between cursor-pointer mb-4 rounded">
                                            <span class="text-lg font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="h-auto flex flex-col justify-start">
                                            <div id="attachmentsContainer">
                                                @foreach ($attachment as $attach)
                                                    <div class="attachment-row flex items-center gap-2" data-attachid="{{ $attach->id }}">
                                                        <a href="{{ url('/attachments/' . $attach->attachfile) }}" target="_blank" class="w-full p-3 mt-4 text-lg border">📎 {{ $attach->name }}</a>
                                                            <button type="button" class="removeAttachment2 mt-4 bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded" data-id="{{ $attach->id }}">🗑️
                                                            </button>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="addAttachment" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-gray-800 border-gray-700 bg-gray-200/10 hover:text-indigo-800 hover:border-indigo-700 hover:bg-indigo-200/10 p-2 ">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v6h6a1 1 0 110 2h-6v6a1 1 0 11-2 0v-6H3a1 1 0 110-2h6V3a1 1 0 011-1z" clip-rule="evenodd"/>
                                                    </svg> Add Attachment
                                            </button>
                                        </div>
                                    </details>
                                </div>
                                <div class="border-b"></div>
                            </div>
                            <div class="flex flex-col w-full ">
                                <div class="flex flex-row w-1/2 pl-8 pr-8 pb-4 gap-4 border-b justify-end w-full">
                                    <div class="w-1/8  flex flex-col justify-end">
                                        <button type="submit" id="#" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-white border-red-600 hover:bg-red-600/10 hover:text-red-600 hover:border-red-600 bg-red-600 p-2 ">
                                            <span id="btnText">Cancel</span>
                                            <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                            </svg>
                                        </button>   
                                    </div>
                                    <div class="w-1/8 flex flex-col justify-end">
                                        <button type="submit" id="submitBtn" class="mt-4 mb-4 flex items-center justify-center  gap-2 rounded border hover:font-medium text-white border-blue-500 hover:bg-blue-500/10 hover:text-blue-500 hover:border-blue-500 bg-blue-500 p-2 ">
                                            <span id="btnText">Submit Approval</span>
                                            <svg id="loadingSpinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
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
        $(document).ready(function () {
            $('#manpowerForm').submit(function (e) {
                e.preventDefault();
    
                let formData = new FormData(this);
                let url = "{{ route('manpowers.update', $manpower->id) }}";

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
                    success: function (response) {
                        //alert("Personnel Requisition Updated Successfully!");
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Manpower Updated Successfully!");
                        window.location.href = "/manpowers";
                    },
                    error: function (xhr) {
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
        $(document).ready(function () {
            let responsibilityCount = 1;
    
            // Fungsi untuk Menambah Baris Responsibility
            $('#addResponsibilities').click(function () {
                responsibilityCount++;
                $('#responsibilitiesTable').append(`
                    <tr class="responsibilities-row">
                        <td class="p-3 border text-center">${responsibilityCount}</td>                   
                       <td class="p-3 border">
                            <input type="date" name="expected_employment_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                        </td>
                        <td class="p-3 border">
                            <input type="text" name="job_title[]" placeholder="Type Job Title..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                        </td>
                        <td class="p-3 border">
                            <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="job_level[]" required>
                                <option value="" disabled selected>Select Job Level</option>
                                @foreach($joblevel as $p)
                                    <option value="{{ $p->title_level }}">{{ $p->title_level }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="p-3 border">
                            <input type="number" min="0" name="qty[]" placeholder="Type Qty..." class="number-only w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                        </td>
                        <td class="p-3 border">                                                               
                            <textarea name="reason_vacancy[]" placeholder="Type Reason..."  class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required></textarea>
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
        // Add Attachment
        $('#addAttachment').click(function () {
            $('#attachmentsContainer').append(`
                <div class="attachment-row flex items-center gap-2">
                    <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded">
                    <button type="button" class="removeAttachment mt-4 bg-red-200/10 dark:bg-red-700/30 hover:border-red-700 hover:bg-red-400/30 border-red-700 border text-white px-3 py-3 rounded">🗑️</button>
                </div>
            `);
        });
    
        // Remove Attachment
        $(document).on('click', '.removeAttachment', function () {
            $(this).closest('.attachment-row').remove();
        });
    
        $(document).on('click', '.removeAttachment2', function () {
            let attachmentId = $(this).data('id'); // Ambil ID attachment
            let row = $(this).closest('.attachment-row'); // Dapatkan row attachment
    
            // Cek konfirmasi pengguna
            let confirmDelete = confirm('Are you sure you want to remove this attachment?');
    
            if (confirmDelete) {
                $.ajax({
                    url: "/manpowers/remove-attachment/" + attachmentId, // Endpoint ke controller
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.success) {
                            row.remove(); // Hapus dari tampilan jika berhasil
                            alert("Attachment removed successfully!");
                        } else {
                            alert("Failed to remove attachment.");
                        }
                    },
                    error: function (xhr) {
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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    
    </x-app-layout>
        