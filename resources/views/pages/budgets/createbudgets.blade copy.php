<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="budgetForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div class="flex w-full flex-col rounded-2xl border-b bg-white p-4 dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="text-xl font-bold">Import Budget</h2>
                                </div>
                                <div
                                    class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Company</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="cpnyid" required>
                                            @foreach ($companies as $p)
                                                <option value="{{ $p->cpnyid }}"> {{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>                                   
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Department</label>
                                        <select
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            name="departementid" required>
                                            @foreach ($departements as $p)
                                                <option value="{{ $p->deptname }}">{{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                                                      
                                    <form action="{{ route('budget.import.post') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="flex items-center gap-4">
                                            <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Import Budget Excel</label>
                                            <input type="file" name="file" id="file" required class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                                Import
                                            </button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                      
                            
                            <div class="flex w-full flex-col gap-2 rounded-2xl border-b bg-white dark:bg-gray-800">
                                <div class="flex w-1/2 w-full flex-col border-b p-4">
                                    <details class="group mb-4" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-lg font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex h-auto flex-col justify-start">
                                            <div id="attachmentsContainer">
                                                <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]"
                                                        class="mt-4 w-full border p-3 text-lg">
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
                                            <span id="cancelText">Cancel</span>
                                            <svg id="cancelSpinner" class="hidden h-5 w-5 animate-spin text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="w-1/8 flex flex-col justify-start">
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
                
            </div>
        </div>
    </div>

    {{-- <script>
        $(document).ready(function() {
            $('#budgetForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('budgets.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#budgetForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Personnel Requisition Submit Successfully!");
                        window.location.href = "/budgets";
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
                    window.location.href = "{{ route('budgets') }}";
                }
            });
        });
    </script> --}}

    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded mt-4">
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
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>    
    
    
</x-app-layout>
