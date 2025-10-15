<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:grid-rows-[minmax(0,auto)_1fr]">
            <div class="flex flex-col gap-8 lg:col-span-2 lg:row-span-1">
                <form id="changestoForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                    @csrf
<<<<<<< Updated upstream
                    <div class="w-full rounded-xl bg-white p-6 dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Create Request Additional
                            </h2>
=======
                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
                        <div class="mb-6 border-b border-gray-200 pb-4 dark:border-gray-700">
                            <h2 class="text-xl font-extrabold text-gray-800 dark:text-white">Create Request Additional</h2>
>>>>>>> Stashed changes
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div class="flex flex-col gap-2">
<<<<<<< Updated upstream
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
=======
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
>>>>>>> Stashed changes
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="cpnyid" required>
                                    @foreach ($usercpny as $p)
                                        <option value="{{ $p->cpnyid }}"
                                            {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>
                                            {{ $p->cpnyid }}</option>
                                    @endforeach
<<<<<<< Updated upstream
                                </select>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="departementid" required>
=======
                                </select>                                
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" name="departementid" required>
>>>>>>> Stashed changes
                                    @foreach ($userdept as $p)
                                        <option value="{{ $p->deptname }}"
                                            {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>
                                            {{ $p->deptname }}</option>
                                    @endforeach
                                </select>
<<<<<<< Updated upstream
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub
                                    Departement</label>
                                <input type="text" name="departement_name" id="departement_name"
                                    class="border-white-300 bg-white-100 w-full rounded-lg border p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    required>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grading</label>
                                <select
                                    class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                    name="subgrade_name" required>
=======
                            </div>           
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sub Departement</label>
                                <input type="text" name="departement_name" id="departement_name" class="w-full rounded-lg border border-white-300 bg-white-100 p-2.5 text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" required>
                            </div>             
                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grading</label>
                                <select class="w-full rounded-lg border border-gray-300 bg-white p-2.5 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" name="subgrade_name" required>
>>>>>>> Stashed changes
                                    <option value="" disabled selected>Select Sub Grading</option>
                                    @foreach ($subgrading as $p)
                                        <option value="{{ $p->subgrade_name }}"> {{ $p->subgrade_name }}</option>
                                    @endforeach
                                </select>
<<<<<<< Updated upstream
                            </div>
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <label class="mt-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                            <textarea name="changerequest_note" id="changerequest_note"
                                class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                required></textarea>
                        </div>
                    </div>

                    <div class="w-full rounded-xl bg-white p-6 dark:bg-gray-800">
=======
                            </div>   
                        </div>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">                           
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">Note</label>
                            <textarea name="changerequest_note" id="changerequest_note" class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800" required></textarea>
                        </div>
                    </div>    

                    <div class="w-full rounded-xl bg-white p-6 shadow-md dark:bg-gray-800">
>>>>>>> Stashed changes
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
                                    <div class="attachment-row flex items-center gap-2">
                                        <input type="file" name="attachments[]"
                                            class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                                        <button type="button"
                                            class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition-colors hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️
                                        </button>
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

                        <div class="flex w-full justify-end gap-4 pt-4">
                            <button type="button" id="cancelBtn"
<<<<<<< Updated upstream
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
=======
                                class="inline-flex items-center justify-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
>>>>>>> Stashed changes
                                <span id="cancelText">Cancel</span>
                                <svg id="cancelSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                            <button type="submit" id="submitBtn"
<<<<<<< Updated upstream
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
=======
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-md transition-colors hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
>>>>>>> Stashed changes
                                <span id="btnText">Submit Approval</span>
                                <svg id="loadingSpinner" class="ml-2 hidden h-5 w-5 animate-spin text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600 lg:col-span-2">
                ChangeSto Created Successfully!
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#changestoForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('changestos.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#changestoForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("ChangeSto Requisition Submit Successfully!");
                        window.location.href = "/changestos";
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
                    window.location.href = "{{ route('changestos') }}";
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
<<<<<<< Updated upstream
                <input type="file" name="attachments[]" class="mt-2 flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">🗑️</button>
=======
                <input type="file" name="attachments[]" class="flex-grow rounded-md border border-gray-200 bg-white px-4 py-2 text-sm text-gray-700 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:file:bg-indigo-700 dark:file:text-white dark:hover:file:bg-indigo-600">
                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">🗑️</button>
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
=======
    </script>   
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>        
>>>>>>> Stashed changes

</x-app-layout>
