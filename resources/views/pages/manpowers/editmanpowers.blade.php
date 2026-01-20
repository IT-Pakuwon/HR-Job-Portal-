<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-4">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-4 px-6 py-4 lg:px-8">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden rounded-lg bg-white sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col dark:bg-gray-800">
                        <form id="manpowerForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div class="flex w-full flex-col pl-8 pr-8 pt-8">
                                <div class="flex justify-between dark:border-gray-600">
                                    <h2 class="mb-4 text-base font-bold">Edit Manpower</h2>
                                    <h2 class="mb-4 text-base font-bold">{{ $manpower->docid }}</h2>
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
                                                    {{ $p->cpnyid == $manpower->cpnyid ? 'selected' : '' }}>
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
                                                    {{ $p->deptname == $manpower->departementid ? 'selected' : '' }}>
                                                    {{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label
                                            class="block font-semibold text-gray-700 dark:text-gray-300">Periode</label>
                                        <input type="text" name="periodyear" id="periodyear"
                                            value={{ $manpower->periodyear }}
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                    </div>
                                    <div class="flex w-full flex-col gap-2">
                                        <label
                                            class="block font-semibold text-gray-700 dark:text-gray-300">Actual</label>
                                        <input type="text" name="actual" id="actual"
                                            value={{ $manpower->actual }}
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
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
                                                        @foreach ($manpowerdetail as $key => $resp)
                                                            <tr class="responsibilities-row">
                                                                <td class="border p-3 text-center">{{ $key + 1 }}
                                                                </td>
                                                                <td class="border p-3">
                                                                    <input type="date"
                                                                        name="expected_employment_date[]"
                                                                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                        value="{{ $resp->expected_employment_date }}"
                                                                        required>
                                                                </td>
                                                                <td class="border p-3">
                                                                    <input type="text" name="job_title[]"
                                                                        placeholder="Type Job Title..."
                                                                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                        value="{{ $resp->job_title }}" required>
                                                                </td>
                                                                <td class="border p-3">
                                                                    <select
                                                                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                        name="job_level[]" required>
                                                                        <option value="" disabled
                                                                            {{ $resp->job_level ? '' : 'selected' }}>
                                                                            Select Job Level</option>
                                                                        @foreach ($joblevel as $p)
                                                                            <option value="{{ $p->title_level }}"
                                                                                {{ $p->title_level == $resp->job_level ? 'selected' : '' }}>
                                                                                {{ $p->title_level }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td class="border p-3">
                                                                    <input type="number" min="0" name="qty[]"
                                                                        placeholder="Type Qty..."
                                                                        class="number-only w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0"
                                                                        value="{{ $resp->qty }}" required>
                                                                </td>
                                                                <td class="border p-3">
                                                                    <textarea name="reason_vacancy[]" placeholder="Type Reason..."
                                                                        class="w-full border-none bg-transparent p-2 focus:outline-none focus:ring-0" required>{{ $resp->reason_vacancy }}</textarea>
                                                                </td>
                                                                <td class="border p-3 text-center">
                                                                    <button type="button"
                                                                        class="removeResponsibilities hidden rounded border border-red-700 bg-red-200/10 px-3 py-3 text-white hover:border-red-700 hover:bg-red-400/30">🗑️</button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button type="button" id="addResponsibilities"
                                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
            $('#manpowerForm').submit(function(e) {
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
                    success: function(response) {
                        //alert("Personnel Requisition Updated Successfully!");
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Manpower Updated Successfully!");
                        window.location.href = "/manpowers";
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
                    url: "/manpowers/remove-attachment/" + attachmentId, // Endpoint ke controller
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
