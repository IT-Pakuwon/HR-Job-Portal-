<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 py-1 lg:px-2">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="taskForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div
                                class="flex w-full w-full flex-col rounded-xl border-b bg-white p-4 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-base font-bold">Create Task</h2>
                                </div>
                                <div
                                    class="mt-2 grid grid-cols-1 gap-6 md:grid-cols-3 lg:grid-cols-3 dark:border-gray-600">
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
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Type</label>
                                        <select name="tasktype" id="tasktype"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                            <option value="">Select Type</option>
                                            <option value="TASK">TASK</option>
                                            <option value="WO">WO</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                        <select id="taskpriority" name="taskpriority"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                            <option value="">Select Priority</option>
                                            <option value="High">High</option>
                                            <option value="Low">Low</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Start
                                            Date</label>
                                        <input type="date" name="startdate" id="startdate"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Due
                                            Date</label>
                                        <input type="date" name="duedate" id="duedate"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>

                                </div>
                                <div class="mt-5 flex items-center gap-4">
                                    <label
                                        class="mb-1 block w-36 font-medium text-gray-700 dark:text-gray-300">Summary</label>
                                    <textarea name="summary" id="summary"
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        required></textarea>
                                </div>
                                <div class="mt-5 flex items-center gap-4">
                                    <label
                                        class="mb-1 block w-36 font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea name="description" id="description"
                                        class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        required></textarea>
                                </div>
                                <div id="participantSection" class="mt-5 flex items-center gap-4">
                                    <label
                                        class="mb-1 block w-36 font-medium text-gray-700 dark:text-gray-300">Participant</label>
                                    <select
                                        class="select2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="participant[]" multiple>
                                        @foreach ($userlist as $p)
                                            <option value="{{ $p->username }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="assignSection" class="mt-5 flex items-center gap-4" style="display: none;">
                                    <label
                                        class="mb-1 block w-36 font-medium text-gray-700 dark:text-gray-300">Assign</label>
                                    <select
                                        class="select2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="assign">
                                        <option value="">Select Assignee</option>
                                        @foreach ($departements as $p)
                                            <option value="{{ $p->deptname }}">{{ $p->deptname }}</option>
                                        @endforeach
                                    </select>
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
                                                class="mb-4 mt-4 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-xs font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
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
                                    </div <div class="w-1/8 flex flex-col justify-start" ">
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
                                >
                            </div>
                    </div>
                </div>
                </form>
            </div>
            <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                Task Created Successfully!
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select Option",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#taskForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);

                // Tampilkan Loading, Disable Button
                $('#submitBtn').attr('disabled', true); // Disable tombol
                $('#btnText').text('Processing...'); // Ubah teks tombol
                $('#loadingSpinner').removeClass('hidden'); // Tampilkan spinner

                $.ajax({
                    url: "{{ route('tasks.store') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#taskForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        window.location.href = "/tasks";
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
                    window.location.href = "{{ route('tasks') }}";
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
        // $(document).ready(function () {
        //     $('#tasktype').on('change', function () {
        //         let selectedType = $(this).val();

        //         if (selectedType === 'TASK') {
        //             $('#participantSection').show();
        //             $('#assignSection').hide();
        //         } else if (selectedType === 'WO') {
        //             $('#participantSection').hide();
        //             $('#assignSection').show();
        //         }S

        //     });
        // });
        $('#tasktype').on('change', function() {
            let selectedType = $(this).val();

            if (selectedType === 'TASK') {
                // Tampilkan participant, sembunyikan assign
                $('#participantSection').show();
                $('#assignSection').hide();

                // Atur required
                // $('select[name="participant[]"]').attr('required', true);
                $('select[name="assign"]').removeAttr('required');
            } else if (selectedType === 'WO') {
                // Tampilkan assign, sembunyikan participant
                $('#participantSection').hide();
                $('#assignSection').show();

                // Atur required
                $('select[name="participant[]"]').removeAttr('required');
                $('select[name="assign"]').attr('required', true);
            } else {
                // Kalau tidak memilih apa-apa
                $('#participantSection').hide();
                $('#assignSection').hide();

                $('select[name="participant[]"]').removeAttr('required');
                $('select[name="assign"]').removeAttr('required');
            }
        });
    </script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>


</x-app-layout>
