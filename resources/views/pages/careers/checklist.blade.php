<div class="overflow-hidden">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-100 dark:border-gray-700/60">
                <th class="w-12 py-2.5 pl-5 pr-3 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">#</th>
                <th class="px-3 py-2.5 text-left text-[10px] font-bold uppercase tracking-widest text-gray-400">Checklist Item</th>
                <th class="py-2.5 pl-3 pr-5 text-right text-[10px] font-bold uppercase tracking-widest text-gray-400">Document</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
            @foreach ($tr_checklist as $p)
                @php $uploaded = !empty($p->checklist_attachfile); @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 {{ $uploaded ? 'bg-emerald-50/30 dark:bg-emerald-900/5' : '' }}">
                    <td class="py-3 pl-5 pr-3 {{ $uploaded ? 'border-l-2 border-emerald-400' : 'border-l-2 border-transparent' }}">
                        <span class="text-xs font-medium {{ $uploaded ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">{{ $p->step_order }}</span>
                    </td>
                    <td class="px-3 py-3 text-sm {{ $uploaded ? 'font-medium text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-300' }}">
                        {{ $p->checklist_descr }}
                    </td>
                    <td class="py-3 pl-3 pr-5">
                        <div class="flex items-center justify-end gap-2">
                            @if ($uploaded)
                                <a href="{{ route('checklist.view', $p->id) }}" target="_blank"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z"/></svg>
                                    View
                                </a>
                            @else
                                <span class="text-xs italic text-gray-400">No document</span>
                            @endif
                            <button class="upload-btn inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 transition hover:border-indigo-300 hover:text-indigo-600 focus:outline-none active:scale-95 dark:border-gray-600 dark:text-gray-400 dark:hover:border-indigo-500 dark:hover:text-indigo-400"
                                data-id="{{ $p->id }}" data-descr="{{ $p->checklist_descr }}">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                Upload
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Upload modal --}}
<div id="uploadModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="w-full max-w-sm rounded-lg bg-white shadow-xl dark:bg-gray-800">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white" id="modalTitle">Upload Document</h3>
        </div>
        <form id="uploadForm" enctype="multipart/form-data" class="p-5">
            @csrf
            <input type="hidden" name="checklist_id" id="checklistId">
            <input type="file" name="document" required
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 focus:border-gray-400 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="cancelModal"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.upload-btn').on('click', function() {
            const checklistId = $(this).data('id');
            const descr = $(this).data('descr');
            $('#checklistId').val(checklistId);
            $('#modalTitle').text(`Upload: ${descr}`);
            $('#uploadModal').removeClass('hidden').addClass('flex');
        });

        $('#uploadForm').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $('#uploadForm button[type="submit"]');
            const cancelBtn = $('#cancelModal');

            submitBtn.prop('disabled', true).html('<span class="animate-pulse">Uploading...</span>');
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
                    submitBtn.prop('disabled', false).html('Upload');
                    cancelBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
                    $('#uploadModal').removeClass('pointer-events-none');
                }
            });
        });
    });

    $(document).on('click', '#cancelModal', function(e) {
        e.preventDefault();
        $('#uploadModal').addClass('hidden').removeClass('flex');
        const f = document.getElementById('uploadForm');
        if (f) f.reset();
    });

    $(document).on('click', '#uploadModal', function(e) {
        if (e.target.id === 'uploadModal') {
            $('#uploadModal').addClass('hidden').removeClass('flex');
            const f = document.getElementById('uploadForm');
            if (f) f.reset();
        }
    });

    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('#uploadModal').addClass('hidden').removeClass('flex');
            const f = document.getElementById('uploadForm');
            if (f) f.reset();
        }
    });
</script>
