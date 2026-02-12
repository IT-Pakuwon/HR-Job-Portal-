<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="manpowerForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div
                                class="flex w-full w-full flex-col rounded-xl border-b bg-white p-4 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-base font-bold">Create Manpower</h2>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
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
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Periode</label>
                                        <input type="text" name="periodyear" id="periodyear"
                                            class="number-only w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="text" name="actual" id="actual"
                                            class="number-only w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <!-- Manpower Detail -->
                            <div class="flex w-full flex-col gap-2 rounded-xl border-b bg-white dark:bg-gray-800">
                                <div class="flex w-full flex-col rounded-xl p-4">
                                    <details class="group" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-sm font-semibold">Details</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex h-auto flex-col justify-start">
                                            <div class="overflow-y-auto">
                                                <table class="mb-4 mt-3 w-full">
                                                    <thead class="bg-gray-100/10">
                                                        <tr>
                                                            <th class="w-12 border p-3 text-center">No</th>
                                                            <th class="border-l border-t p-3">Expected Date</th>
                                                            <th class="border-l border-t p-3">Job Title</th>
                                                            <th class="border-l border-t p-3">Job Level</th>
                                                            <th class="border-l border-t p-3">Qty</th>
                                                            <th class="border-l border-t p-3">Justification</th>
                                                            <th class="w-16 border-r border-t p-3 text-center"></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="responsibilitiesTable">
                                                        <tr class="responsibilities-row">
                                                            <td class="border p-3 text-center">1</td>
                                                            <td class="border p-3">
                                                                <input type="date" name="expected_employment_date[]"
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    required>
                                                            </td>
                                                            <td class="border p-3">
                                                                <input type="text" name="job_title[]"
                                                                    placeholder="Type Job Title..."
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    required>
                                                            </td>
                                                            <td class="border p-3">
                                                                <select
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    name="job_level[]" required>
                                                                    <option value="" disabled selected>Select Job
                                                                        Level</option>
                                                                    @foreach ($joblevel as $p)
                                                                        <option value="{{ $p->title_level }}">
                                                                            {{ $p->title_level }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td class="border p-3">
                                                                <input type="number" min="0" name="qty[]"
                                                                    placeholder="Type Qty..."
                                                                    class="number-only w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                    required>
                                                            </td>
                                                            <td class="border p-3">
                                                                <textarea name="reason_vacancy[]" placeholder="Type Reason..."
                                                                    class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" required></textarea>
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
                                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                <div class="flex w-1/2 w-full flex-col border-b p-4">
                                    <details class="group mb-4" hide>
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
                                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                        <button type="submit" id="#"
                                            class="flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                                            <span id="btnText">Cancel Approval</span>
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
                                    <div class="w-1/8 flex flex-col justify-start">
                                        <button type="submit" id="submitBtn"
                                            class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
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
                    </form>
                </div>
                <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                    Manpower Created Successfully!
                </div>
            </div>
        </div>
    </div>



    <script>
        $(document).ready(function() {
            $('#manpowerForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('manpowers.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#manpowerForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Manpower Submit Successfully!");
                        window.location.href = "/manpowers";
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
        });
    </script>

    <script>
        $(document).ready(function() {
            // Fungsi Tambah Attachment
            $('#addAttachment').click(function() {
                $('#attachmentsContainer').append(`
            <div class="attachment-row flex items-center gap-2">
                <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-sm border rounded mt-4">
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
                        <input type="date" name="expected_employment_date[]" class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>                                                                
                    </td>
                    <td class="p-3 border">
                        <input type="text" name="job_title[]" placeholder="Type Job Title..." class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" required>
                    </td>
                    <td class="p-3 border">
                        <select class="w-full p-2 border-none focus:ring-0 focus:outline-none bg-transparent" name="job_level[]" required>
                            <option value="" disabled selected>Select Job Level</option>
                            @foreach ($joblevel as $p)
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

</x-app-layout>
