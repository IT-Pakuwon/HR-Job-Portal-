<table class="min-w-full border border-gray-300 text-sm dark:border-gray-600">
    <thead class="bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
        <tr>
            <th class="border px-3 py-2">No</th>
            <th class="border px-3 py-2">Checklist Item</th>
            <th class="border px-3 py-2">Document</th>
            {{-- <th class="border px-3 py-2">Action</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($tr_checklist as $p)
            <tr>
                <td class="border px-3 py-2">{{ $p->step_order }}</td>
                <td class="border px-3 py-2">{{ $p->checklist_descr }}</td>
                <td class="border px-3 py-2">
                    @if ($p->checklist_filename)
                        📁 <a href="{{ asset('attachments/' . $year . '/' . $p->checklist_attachfile) }}" target="_blank"
                            class="text-blue-600 underline">View</a>
                    @else
                        <span class="italic text-gray-400">No document</span>
                    @endif
                    <button class="upload-btn rounded bg-indigo-500 px-3 py-1 text-xs text-white hover:bg-indigo-600"
                        data-id="{{ $p->id }}" data-descr="{{ $p->checklist_descr }}">
                        Upload
                    </button>
                </td>
                {{-- <td class="border px-3 py-2">
                    <button class="upload-btn bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded text-xs"
                            data-id="{{ $p->id }}" data-descr="{{ $p->checklist_descr }}">
                        Upload
                    </button>
                </td> --}}
            </tr>
        @endforeach
    </tbody>
</table>
<div id="uploadModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white" id="modalTitle">Upload Document</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="checklist_id" id="checklistId">
            <input type="file" name="document" required class="mb-4 w-full text-sm text-gray-700 dark:text-white">
            <div class="flex justify-end gap-2">
                <button type="button" id="cancelModal" class="rounded bg-gray-400 px-4 py-2 text-white">Cancel</button>
                <button type="submit" class="rounded bg-indigo-600 px-4 py-2 text-white">Upload</button>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        // Tampilkan modal
        $('.upload-btn').on('click', function() {
            const checklistId = $(this).data('id');
            const descr = $(this).data('descr');
            $('#checklistId').val(checklistId);
            $('#modalTitle').text(`Upload Document: ${descr}`);
            $('#uploadModal').removeClass('hidden').addClass('flex');
        });

        // Tutup modal
        $('#cancelModal').on('click', function() {
            $('#uploadModal').addClass('hidden').removeClass('flex');
        });

        // Upload form
        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '{{ route('checklist.upload') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        location.reload(); // Refresh untuk update tampilan
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    alert('Upload failed. Try again.');
                }
            });
        });
    });
</script>
