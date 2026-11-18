@php
    // Resolve each item's document state first so the header can show overall progress.
    $cv = $cv ?? null;
    $ijazah = $ijazah ?? null;
    $transkip = $transkip ?? null;
    $prfPersonnel = $prfPersonnel ?? null;
    $checkItems = $tr_checklist->map(function ($p) use ($cv, $ijazah, $transkip, $prfPersonnel) {
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

        return (object) [
            'row' => $p,
            'isPrfItem' => $isPrfItem,
            'autoLinkLabel' => $autoLinkLabel,
            'autoLinkUrl' => $autoLinkUrl,
            'isAutoLinkItem' => $isPrfItem || $autoLinkLabel !== null,
            'uploaded' => $isPrfItem
                ? (bool) ($prfPersonnel ?? null)
                : ($autoLinkLabel !== null ? !empty($autoLinkUrl) : !empty($p->checklist_attachfile)),
        ];
    });
    $checkTotal = $checkItems->count();
    $checkDone = $checkItems->where('uploaded', true)->count();
    $checkPct = $checkTotal ? (int) round($checkDone / $checkTotal * 100) : 0;
    $dlIcon = 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3';
@endphp

{{-- ── Header + progress ──────────────────────────────────────────── --}}
<div class="px-5 py-4">
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Checklist</h3>
                <p class="text-xs text-gray-400">Required documents for this applicant</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-sm font-bold {{ $checkPct === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-800 dark:text-gray-100' }}">{{ $checkDone }}<span class="font-medium text-gray-400"> / {{ $checkTotal }}</span></p>
            <p class="text-[11px] uppercase tracking-widest text-gray-400">completed</p>
        </div>
    </div>
    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
        <div class="h-full rounded-full transition-all duration-500 {{ $checkPct === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $checkPct }}%"></div>
    </div>
</div>

{{-- ── Items ──────────────────────────────────────────────────────── --}}
<div class="space-y-2.5 px-5 pb-5"
    x-data="{
        page: 1, per: 10, total: {{ $checkTotal }},
        get pages() { return Math.max(1, Math.ceil(this.total / this.per)); },
        get from() { return this.total ? (this.page - 1) * this.per + 1 : 0; },
        get to() { return Math.min(this.page * this.per, this.total); },
        show(i) { return i >= (this.page - 1) * this.per && i < this.page * this.per; },
        go(p) { this.page = Math.min(this.pages, Math.max(1, p)); },
        get nums() {
            const n = [], p = this.page, last = this.pages;
            for (let i = 1; i <= last; i++) {
                if (i === 1 || i === last || Math.abs(i - p) <= 1) n.push(i);
                else if (n[n.length - 1] !== '…') n.push('…');
            }
            return n;
        }
    }">
    @forelse ($checkItems as $it)
        @php $p = $it->row; $uploaded = $it->uploaded; @endphp
        <div x-show="show({{ $loop->index }})" @if(!$loop->first) x-cloak @endif class="flex flex-wrap items-center gap-3 rounded-2xl border p-3.5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md sm:flex-nowrap
            {{ $uploaded ? 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-500/30 dark:bg-emerald-500/5' : 'border-gray-100 bg-white dark:border-gray-700/60 dark:bg-gray-800' }}">

            {{-- Status marker --}}
            @if ($uploaded)
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm shadow-emerald-500/30">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </span>
            @else
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-dashed border-gray-300 text-sm font-bold text-gray-400 dark:border-gray-600">{{ $p->step_order }}</span>
            @endif

            {{-- Title --}}
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold {{ $uploaded ? 'text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-200' }}">{{ $p->checklist_descr }}</p>
                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $uploaded ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300' }}">{{ $uploaded ? 'Uploaded' : 'Pending' }}</span>
                    @if ($it->isAutoLinkItem)
                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                            Auto-linked
                        </span>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                @if ($it->isPrfItem)
                    @if ($prfPersonnel)
                        <a href="{{ route('checklist.prf-pdf', $prfPersonnel->docid) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100 dark:bg-indigo-500/15 dark:text-indigo-300 dark:hover:bg-indigo-500/25">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dlIcon }}"/></svg>
                            Download PDF
                        </a>
                        <a href="{{ url('/showpersonnels/'.$prfHash) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            View PRF
                        </a>
                    @else
                        <span class="text-xs italic text-gray-400">No PRF document found</span>
                    @endif
                @elseif ($it->autoLinkLabel)
                    @if ($it->autoLinkUrl)
                        <a href="{{ $it->autoLinkUrl }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:hover:bg-emerald-500/25">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dlIcon }}"/></svg>
                            Download
                        </a>
                    @else
                        <span class="text-xs italic text-gray-400">No {{ $it->autoLinkLabel }} document found</span>
                    @endif
                @else
                    @if ($uploaded)
                        <a href="{{ route('checklist.view', $p->id) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100 dark:bg-emerald-500/15 dark:text-emerald-300 dark:hover:bg-emerald-500/25">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $dlIcon }}"/></svg>
                            Download
                        </a>
                    @endif
                    <button class="upload-btn inline-flex items-center gap-1.5 rounded-lg {{ $uploaded ? 'border border-gray-200 text-gray-500 hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-600 dark:text-gray-400' : 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30 hover:bg-indigo-700' }} px-3 py-1.5 text-xs font-semibold transition focus:outline-none active:scale-95"
                        data-id="{{ $p->id }}" data-descr="{{ $p->checklist_descr }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                        {{ $uploaded ? 'Replace' : 'Upload' }}
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-200 py-14 text-center dark:border-gray-700">
            <span class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-2xl dark:bg-emerald-500/15">📎</span>
            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">No checklist items</p>
        </div>
    @endforelse

    {{-- Pagination (10 per page) --}}
    <div x-show="total > per" x-cloak class="flex flex-wrap items-center justify-between gap-3 pt-2">
        <p class="text-xs text-gray-400">Showing <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="from + '–' + to"></span> of <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="total"></span></p>
        <nav class="flex items-center gap-1" aria-label="Checklist pages">
            <button type="button" @click="go(page - 1)" :disabled="page === 1"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Previous page">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </button>
            <template x-for="(n, idx) in nums" :key="idx">
                <button type="button" @click="typeof n === 'number' && go(n)" :disabled="n === '…'"
                    :class="n === page ? 'border-indigo-600 bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700'"
                    class="flex h-8 min-w-8 items-center justify-center rounded-lg border px-2 text-xs font-semibold transition disabled:cursor-default disabled:border-transparent disabled:hover:bg-transparent"
                    x-text="n"></button>
            </template>
            <button type="button" @click="go(page + 1)" :disabled="page === pages"
                class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Next page">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
            </button>
        </nav>
    </div>
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
                class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 focus:border-gray-400 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <div class="mt-4 flex justify-end gap-2">
                <button type="button" id="cancelModal"
                    class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-500 transition hover:bg-gray-50 focus:outline-none dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-700 focus:outline-none active:scale-95 dark:bg-white dark:text-gray-900">
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
