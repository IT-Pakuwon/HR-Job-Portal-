<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <div class="mb-4 flex items-center justify-end sm:mb-0"></div>
        <div class="mb-2 mt-2 rounded-xl bg-white p-6 dark:bg-gray-800">
            <h2 class="mb-4 mt-6 bg-white text-2xl font-bold">Create Tasks</h2>
            <form id="taskForm" enctype="multipart/form-data" class="space-y-6 rounded-lg bg-white p-6">
                @csrf
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block font-semibold text-gray-700">Company</label>
                        <select class="w-full rounded-lg border p-3 focus:ring focus:ring-blue-300" name="cpnyid"
                            required>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpnyid }}"
                                    {{ $p->cpnyid == $usercpny2->cpnyid ? 'selected' : '' }}>{{ $p->cpnyid }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Department</label>
                        <select class="w-full rounded-lg border p-3 focus:ring focus:ring-blue-300" name="departementid"
                            required>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->deptname }}"
                                    {{ $p->deptname == $userdept2->deptname ? 'selected' : '' }}>{{ $p->deptname }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Type</label>
                        <select class="w-full rounded-lg border p-3 focus:ring focus:ring-blue-300" name="tasktype"
                            required>
                            <option value="">Select Task</option>
                            <option value="Task">Task</option>
                            <option value="WO">WO</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Summary</label>
                        <textarea name="summary" id="summary" class="w-full rounded border p-2"></textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700">Description</label>
                        <textarea name="description" id="description" class="w-full rounded border p-2"></textarea>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700">Participant</label>
                        {{-- <select class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300" name="participant" required> --}}
                        <select class="select2 w-full rounded-lg border p-3 focus:ring focus:ring-blue-300"
                            name="participant[]" multiple required>
                            @foreach ($userlist as $p)
                                <option value="{{ $p->username }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700">Start Date</label>
                        <input type="date" name="startdate" id="startdate" class="w-full rounded border p-2">
                    </div>
                    <div>
                        <label class="block text-gray-700">Due Date</label>
                        <input type="date" name="duedate" id="duedate" class="w-full rounded border p-2">
                    </div>
                </div>

                <!-- Attachments -->
                <div class="mt-6">
                    <label class="block text-lg font-semibold">Attachments</label>
                    <div id="attachmentsContainer">
                        <div class="attachment-row flex items-center gap-2">
                            <input type="file" name="attachments[]" class="w-full rounded-lg border p-3 text-lg">
                            <button type="button"
                                class="removeAttachment hidden rounded border border-red-600 bg-red-200/30 p-3 text-red-600 transition hover:bg-red-600 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                            <button type="button" id="addAttachment"
                                class="rounded border border-blue-600 bg-blue-200/30 p-3 text-blue-600 hover:bg-blue-600 hover:text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" id="submitBtn"
                        class="flex items-center gap-2 rounded bg-blue-500 px-4 py-2 text-white">
                        <span id="btnText">Submit Approval</span>
                        <svg id="loadingSpinner" class="hidden h-5 w-5 animate-spin text-white"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </button>
                </div>
            </form>

            <div id="successMessage" class="mt-4 hidden font-bold text-green-600">
                Tasks Created Successfully!
            </div>
        </div>
        <div class="mb-8 sm:flex sm:items-center sm:justify-between"></div>
        <div class="mb-4 flex items-center justify-end sm:mb-0"></div>
    </div>

    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select Participants",
                allowClear: true
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


</x-app-layout>
