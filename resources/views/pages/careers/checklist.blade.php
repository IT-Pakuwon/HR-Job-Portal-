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
                @php
                    $descrUpper = strtoupper(trim((string) $p->checklist_descr));
                    $isPrfItem = $descrUpper === 'PRF';

                    $autoLinkLabel = null;
                    $autoLinkUrl = null;
                    if (!$isPrfItem) {
                        if (str_contains($descrUpper, 'CV') || str_contains($descrUpper, 'RIWAYAT HIDUP')) {
                            $autoLinkLabel = 'CV';
                            $autoLinkUrl = $cv ?? null;
                        } elseif (str_contains($descrUpper, 'IJAZAH')) {
                            $autoLinkLabel = 'Ijazah';
                            $autoLinkUrl = $ijazah ?? null;
                        } elseif (str_contains($descrUpper, 'TRANSKIP') || str_contains($descrUpper, 'TRANSKRIP')) {
                            $autoLinkLabel = 'Transkrip Nilai';
                            $autoLinkUrl = $transkip ?? null;
                        }
                    }
                    $isAutoLinkItem = $isPrfItem || $autoLinkLabel !== null;

                    $uploaded = $isPrfItem
                        ? (bool) ($prfPersonnel ?? null)
                        : ($autoLinkLabel !== null ? !empty($autoLinkUrl) : !empty($p->checklist_attachfile));
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/20 {{ $uploaded ? 'bg-emerald-50/30 dark:bg-emerald-900/5' : '' }}">
                    <td class="py-3 pl-5 pr-3 {{ $uploaded ? 'border-l-2 border-emerald-400' : 'border-l-2 border-transparent' }}">
                        <span class="text-xs font-medium {{ $uploaded ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">{{ $p->step_order }}</span>
                    </td>
                    <td class="px-3 py-3 text-sm {{ $uploaded ? 'font-medium text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-300' }}">
                        {{ $p->checklist_descr }}
                        @if ($isAutoLinkItem)
                            <span class="ml-1.5 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">Auto-linked</span>
                        @endif
                    </td>
                    <td class="py-3 pl-3 pr-5">
                        @if ($isPrfItem)
                            <div class="flex items-center justify-end gap-3">
                                @if ($prfPersonnel)
                                    <a href="{{ route('checklist.prf-pdf', $prfPersonnel->docid) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download PDF
                                    </a>
                                    <a href="{{ url('/showpersonnels/'.$prfHash) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                        View PRF
                                    </a>
                                @else
                                    <span class="text-xs italic text-gray-400">No PRF document found</span>
                                @endif
                            </div>
                        @elseif ($autoLinkLabel)
                            <div class="flex items-center justify-end gap-3">
                                @if ($autoLinkUrl)
                                    <a href="{{ $autoLinkUrl }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
                                    </a>
                                @else
                                    <span class="text-xs italic text-gray-400">No {{ $autoLinkLabel }} document found</span>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-end gap-2">
                                @if ($uploaded)
                                    <a href="{{ route('checklist.view', $p->id) }}"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 transition hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                        Download
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
                        @endif
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
                    class="rounded-lg border border-gray-200 px-4 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
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
