<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-1">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-0 px-1 py-4 lg:px-8">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col">
                        <form id="taskForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div
                                class="flex w-full w-full flex-col rounded-xl border-b bg-white p-4 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-base font-bold">Edit Tasks</h2>
                                    <h2 class="mb-4 text-base font-bold">{{ $task->docid }}</h2>
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
                                                    {{ $p->cpnyid == $task->cpnyid ? 'selected' : '' }}>
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
                                                    {{ $p->deptname == $task->departementid ? 'selected' : '' }}>
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
                                            <option value="" {{ $task->tasktype == '' ? 'selected' : '' }}>
                                            </option>
                                            <option value="TASK" {{ $task->tasktype == 'TASK' ? 'selected' : '' }}>
                                                TASK</option>
                                            <option value="WO" {{ $task->tasktype == 'WO' ? 'selected' : '' }}>WO
                                            </option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Priority</label>
                                        <select id="taskpriority" name="taskpriority"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                            <option value="High"
                                                {{ $task->taskpriority == 'High' ? 'selected' : '' }}>High</option>
                                            <option value="Low"
                                                {{ $task->taskpriority == 'Low' ? 'selected' : '' }}>Low</option>
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Summary</label>
                                        <textarea name="summary" id="summary"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>{{ $task->summary }}</textarea>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Description</label>
                                        <textarea name="description" id="description"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>{{ $task->description }}</textarea>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Start
                                            Date</label>
                                        <input type="date" name="startdate" id="startdate"
                                            value="{{ $task->startdate }}"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Due
                                            Date</label>
                                        <input type="date" name="duedate" id="duedate"
                                            value="{{ $task->duedate }}"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>
                                    </div>

                                </div>
                                <div class="mt-5 flex items-center gap-4">
                                    <label
                                        class="mb-1 block w-36 font-medium text-gray-700 dark:text-gray-300">Participant</label>
                                    <select
                                        class="select2 w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                        name="participant[]" multiple required>
                                        @foreach ($userlist as $sub)
                                            <option value="{{ $sub->email }}"
                                                @foreach ($participantlist_user as $sublist){{ $sublist == $sub->email ? 'selected' : '' }} @endforeach>
                                                {{ $sub->name }}</option>
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
                                                {{-- <div class="attachment-row flex items-center gap-2">
                                                    <input type="file" name="attachments[]" class="w-full p-3 mt-4 text-sm border">
                                                    <button type="button" class="removeAttachment bg-red-200/30 mt-4 text-red-600 p-3 rounded hidden border border-red-600 hover:text-white hover:bg-red-600 transition">
                                                        🗑️
                                                    </button>
                                                </div> --}}
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
                                        <button type="submit" id="#"
                                            class="mb-4 mt-4 flex items-center justify-center gap-2 rounded border border-red-700 bg-red-200/10 p-2 text-red-700 hover:border-red-700 hover:bg-red-700 hover:font-medium hover:text-white">
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
                    </div>
                    </form>
                </div>
                <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                    Task Updated Successfully!
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select Participants",
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
                let url = "{{ route('tasks.update', $task->id) }}";

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
                        $('#successMessage').removeClass('hidden'); // Tampilkan pesan sukses
                        $('#taskForm')[0].reset(); // Reset form setelah submit

                        // Reset Tombol ke Semula
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("Tasks Updated Successfully!");
                        window.location.href = "/tasks";
                    },
                    error: function(xhr) {
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
        $(document).on('click', '.removeAttachment2', function() {
            let attachmentId = $(this).data('id'); // Ambil ID attachment
            let row = $(this).closest('.attachment-row'); // Dapatkan row attachment

            // Cek konfirmasi pengguna
            let confirmDelete = confirm('Are you sure you want to remove this attachment?');

            if (confirmDelete) {
                $.ajax({
                    url: "/tasks/remove-attachment/" + attachmentId, // Endpoint ke controller
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove(); // Hapus dari tampilan jika berhasil                       
                            toastr.success("Attachment removed successfully!");
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
