<x-app-layout>
    <div class="max-w-9xl mx-auto w-full py-4">
        <div class="grid">
            <div class="max-w-9xl mx-auto w-full px-4 px-6 py-4 lg:px-8">
                <div class="gap-6">
                    <div
                        class="flex flex-col gap-10 overflow-hidden rounded-lg bg-white sm:col-span-1 lg:row-span-2 xl:col-span-1 xl:flex-col dark:bg-gray-800">
                        <form id="newsForm" class="flex flex-col gap-4" enctype="multipart/form-data">
                            @csrf
                            <div
                                class="flex w-full w-full flex-col rounded-2xl border-b bg-white p-6 shadow-sm dark:bg-gray-800">
                                <div class="flex justify-between border-b dark:border-gray-600">
                                    <h2 class="mb-2 text-xl font-bold">Edit News</h2>
                                    <h2 class="mb-4 text-xl font-bold">{{ $news->docid }}</h2>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">
                                    <input type="hidden" name="_method" value="PUT">
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Company</label>
                                        <select name="cpnyid"
                                            class="select2 flex-1 rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            @foreach ($usercpny as $p)
                                                <option value="{{ $p->cpnyid }}"
                                                    {{ $p->cpnyid == $news->cpnyid ? 'selected' : '' }}>
                                                    {{ $p->cpnyid }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Department</label>
                                        <select name="departementid"
                                            class="select2 flex-1 rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">
                                            @foreach ($userdept as $p)
                                                <option value="{{ $p->deptname }}"
                                                    {{ $p->deptname == $news->departementid ? 'selected' : '' }}>
                                                    {{ $p->deptname }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Title</label>
                                        <textarea name="title" id="title"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800"
                                            required>{{ $news->title }}</textarea>
                                    </div>
                                </div>
                                <div
                                    class="mt-2 mt-2 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2 dark:border-gray-600">

                                    <div class="flex items-center gap-4">
                                        <label
                                            class="mb-1 block w-40 font-medium text-gray-700 dark:text-gray-300">Description</label>
                                        <textarea name="description" id="description"
                                            class="w-full rounded-sm border border-gray-200/50 bg-gray-200/10 p-3 focus:ring focus:ring-blue-300 dark:bg-gray-800">{{ $news->description }}</textarea>
                                    </div>
                                </div>

                            </div>

                            <div class="flex w-full flex-col gap-2 rounded-2xl pl-8 pr-8 pt-4">
                                <div class="flex w-full flex-col">
                                    <details class="group mb-4" open>
                                        <summary class="mb-4 flex cursor-pointer items-center justify-between rounded">
                                            <span class="text-lg font-semibold">Attachments</span>
                                            <span class="transition-all group-open:hidden">See details</span>
                                            <span class="hidden transition-all group-open:inline">Hide details</span>
                                        </summary>
                                        <div class="flex h-auto flex-col justify-start">
                                            <div id="attachmentsContainer">
                                                @foreach ($attachment as $attach)
                                                    <div class="attachment-row flex items-center gap-2"
                                                        data-attachid="{{ $attach->id }}">
                                                        <a href="{{ url('/attachments/' . $attach->attachfile) }}"
                                                            target="_blank" class="mt-4 w-full border p-3 text-lg">📎
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
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
            $('#newsForm').submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let url = "{{ route('news.update', $news->id) }}";

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
                        //alert("News Requisition Updated Successfully!");
                        $('#submitBtn').attr('disabled', false);
                        $('#btnText').text('Submit Approval');
                        $('#loadingSpinner').addClass('hidden'); // Sembunyikan spinner
                        toastr.success("News Requisition Updated Successfully!");
                        window.location.href = "/news";
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
        // Add Attachment
        $('#addAttachment').click(function() {
            $('#attachmentsContainer').append(`
                <div class="attachment-row flex items-center gap-2">
                    <input type="file" name="attachments[]" class="w-full mt-4 p-3 text-lg border rounded">
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
                    url: "/news/remove-attachment/" + attachmentId, // Endpoint ke controller
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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="no-referrer"></script>
    <script>
        tinymce.init({
            selector: '#description',
            height: 250,
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | formatselect | bold italic underline | bullist numlist | link image | preview',
            skin: 'oxide',
            content_css: 'default',
        });
    </script>


</x-app-layout>
