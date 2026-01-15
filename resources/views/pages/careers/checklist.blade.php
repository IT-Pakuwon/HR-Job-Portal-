<table class="min-w-full border border-gray-300 text-xs dark:border-gray-600">
    <thead>
        <tr class="bg-gray-50 text-gray-600 dark:bg-gray-700 dark:text-gray-700">
            <th class="px-4 py-3 text-left font-semibold">No</th>
            <th class="px-4 py-3 text-left font-semibold">Checklist Item</th>
            <th class="px-4 py-3 text-left font-semibold">Document</th>
            {{-- <th class="border px-3 py-2">Action</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($tr_checklist as $p)
            <tr>
                <td class="border px-3 py-2">{{ $p->step_order }}</td>
                <td class="border px-3 py-2">{{ $p->checklist_descr }}</td>
                {{-- <td class="border px-3 py-2">
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
                </td>                --}}
                <td class="border px-3 py-2">
                    @if ($p->checklist_attachfile)
                        📁 <a href="{{ route('checklist.view', $p->id) }}" target="_blank"
                            class="text-blue-600 underline">
                            View
                        </a>
                        {{-- @if ($p->checklist_filename)
                        <span class="ml-2 text-gray-500">({{ $p->checklist_filename }})</span>
                        @endif --}}
                    @else
                        <span class="italic text-gray-400">No document</span>
                    @endif

                    <button class="upload-btn rounded bg-indigo-500 px-3 py-1 text-xs text-white hover:bg-indigo-600"
                        data-id="{{ $p->id }}" data-descr="{{ $p->checklist_descr }}">
                        Upload
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
<div id="uploadModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
    <div class="w-full max-w-md rounded-lg bg-white p-4 dark:bg-gray-800">
        <h3 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white" id="modalTitle">Upload Document</h3>
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="checklist_id" id="checklistId">
            <input type="file" name="document" required class="mb-4 w-full text-xs text-gray-700 dark:text-white">
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

        // // Tutup modal
        // $('#cancelModal').on('click', function() {
        //     $('#uploadModal').addClass('hidden').removeClass('flex');
        // });

        // Upload form
        // $('#uploadForm').on('submit', function(e) {
        //     e.preventDefault();
        //     const formData = new FormData(this);

        //     $.ajax({
        //         url: '{{ route('checklist.upload') }}',
        //         method: 'POST',
        //         data: formData,
        //         processData: false,
        //         contentType: false,
        //         success: function(res) {
        //             if (res.success) {
        //                 location.reload(); // Refresh untuk update tampilan
        //             } else {
        //                 alert(res.message);
        //             }
        //         },
        //         error: function() {
        //             alert('Upload failed. Try again.');
        //         }
        //     });
        // });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = $('#uploadForm button[type="submit"]');
            const cancelBtn = $('#cancelModal');

            // Disable tombol dan tampilkan loading
            submitBtn.prop('disabled', true)
                .html('<span class="animate-pulse">Uploading...</span>');
            cancelBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            $('#uploadModal').addClass('pointer-events-none');

            $.ajax({
                url: '{{ route('checklist.upload') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    alert('Upload failed. Try again.');
                },
                complete: function() {
                    // Kembalikan tombol ke kondisi semula
                    submitBtn.prop('disabled', false).html('Upload');
                    cancelBtn.prop('disabled', false).removeClass(
                        'opacity-50 cursor-not-allowed');
                    $('#uploadModal').removeClass('pointer-events-none');

                }
            });
        });

    });
</script>
<script>
    // Tutup via tombol Cancel
    $(document).on('click', '#cancelModal', function(e) {
        e.preventDefault();
        $('#uploadModal').addClass('hidden').removeClass('flex');
        const f = document.getElementById('uploadForm');
        if (f) f.reset();
    });

    // Tutup dengan klik overlay (area gelap di luar card)
    $(document).on('click', '#uploadModal', function(e) {
        if (e.target.id === 'uploadModal') {
            $('#uploadModal').addClass('hidden').removeClass('flex');
            const f = document.getElementById('uploadForm');
            if (f) f.reset();
        }
    });

    // Tutup dengan tombol ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#uploadModal').addClass('hidden').removeClass('flex');
            const f = document.getElementById('uploadForm');
            if (f) f.reset();
        }
    });
</script>
