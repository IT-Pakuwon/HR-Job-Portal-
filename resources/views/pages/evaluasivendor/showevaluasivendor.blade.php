<x-app-layout>
    @php
        $formatDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->translatedFormat('d F Y') : '—';
        $statusClass = match ($evaluation->status) {
            'C' => 'bg-green-100 text-green-700 dark:bg-green-800/30 dark:text-green-300',
            'R', 'X' => 'bg-red-100 text-red-700 dark:bg-red-800/30 dark:text-red-300',
            'D' => 'bg-blue-100 text-blue-700 dark:bg-blue-800/30 dark:text-blue-300',
            'P' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800/30 dark:text-yellow-300',
            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-300',
        };
        $kpiScore = (float) $evaluation->template_vpe_aspect_grandtotal_score;
        [$kpiAccent, $kpiTint] = match (true) {
            $kpiScore >= 90 => ['#15803d', '#f0fdf4'],
            $kpiScore >= 80 => ['#4338ca', '#eef2ff'],
            $kpiScore >= 70 => ['#a16207', '#fffbeb'],
            $kpiScore >= 60 => ['#c2410c', '#fff7ed'],
            default => ['#b91c1c', '#fef2f2'],
        };
    @endphp
    <div class="mx-auto w-full space-y-4 p-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Home</a><span>/</span>
                <a href="{{ route('evaluasivendor') }}" class="hover:text-indigo-600">Penilaian Vendor</a><span>/</span>
                <span class="font-semibold text-gray-800 dark:text-gray-100">Show Details</span>
            </nav>
            <x-approval-actions :status="$evaluation->status" :is-approver="$isApprover" />
        </div>
        @if(session('success'))<div role="status" class="rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section class="overflow-hidden rounded-xl bg-white dark:bg-gray-800" style="min-height:360px">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700">
                    <h1 class="flex flex-wrap items-center gap-2 text-sm font-bold"><span class="rounded bg-purple-100 px-2 py-1 text-purple-700">ID</span>{{ $evaluation->vendor_perfomance_id }}<span class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 dark:bg-green-800/30 dark:text-green-300">{{ $evaluation->vendor_perfomance_type }}</span></h1>
                    <div class="flex items-center gap-3">
                        <span class="{{ $statusClass }} rounded-full px-4 py-1 text-xs font-semibold">{{ $statuses[$evaluation->status] ?? $evaluation->status }}</span>
                        <a href="{{ route('evaluasivendor.print', $hash) }}" target="_blank" rel="noopener noreferrer" class="rounded-full bg-indigo-600 px-4 py-1 text-xs font-semibold text-white">Print PDF</a>
                    </div>
                </header>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 text-sm sm:grid-cols-2">
                    @foreach([
                        'Company' => $evaluation->cpny_id,
                        'Department' => $evaluation->department_id,
                        'Tanggal Evaluasi' => $formatDate($evaluation->vendor_perfomance_date),
                        'Created User' => $evaluation->created_by,
                        $sourceLabel => $evaluation->ponbr,
                        'Vendor' => $evaluation->vendorname,
                        'Periode Mulai' => $formatDate($evaluation->vendor_perfomance_start_date),
                        'Periode Selesai' => $formatDate($evaluation->vendor_perfomance_end_date),
                    ] as $label => $value)
                        <div class="grid grid-cols-2 gap-2"><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="break-words font-medium">@if($label === $sourceLabel && $sourceUrl)<a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-600 hover:underline">{{ $value }}</a>@else{{ $value ?: '—' }}@endif</dd></div>
                    @endforeach
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700"><dt class="text-xs text-gray-500 dark:text-gray-400">Jenis Pekerjaan / Template</dt><dd class="mt-1 font-medium">{{ $evaluation->template_vpe_descr ?: '—' }}</dd></div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700"><dt class="text-xs text-gray-500 dark:text-gray-400">Pekerjaan</dt><dd class="mt-1 whitespace-pre-line">{{ $evaluation->keperluan ?: '—' }}</dd></div>
                </dl>
            </section>
            <section x-data="{ tab: 'notes' }" class="overflow-hidden rounded-xl bg-white dark:bg-gray-800" style="min-height:400px;display:flex;flex-direction:column">
                <div class="flex border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700" role="tablist" aria-label="Informasi penilaian">
                    <button type="button" @click="tab = 'notes'" :aria-selected="tab === 'notes'" :class="tab === 'notes' ? 'border-indigo-600 text-indigo-600' : 'border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-sm" role="tab">Catatan Vendor</button>
                    <button type="button" @click="tab = 'approval'" :aria-selected="tab === 'approval'" :class="tab === 'approval' ? 'border-indigo-600 text-indigo-600' : 'border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-sm" role="tab">Approval Details</button>
                    <button type="button" @click="tab = 'comments'; $dispatch('load-vendor-comments')" :aria-selected="tab === 'comments'" :class="tab === 'comments' ? 'border-indigo-600 text-indigo-600' : 'border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-sm" role="tab">Comments <span id="vendor-comment-count" hidden style="margin-left:4px;padding:2px 5px;border-radius:999px;background:#4f35ff;color:white;font-size:10px;font-weight:700;line-height:1"></span></button>
                </div>
                <div x-show="tab === 'approval'" class="overflow-x-auto p-4" role="tabpanel">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs text-gray-500"><tr><th class="p-3">Level</th><th class="p-3">Name</th><th class="p-3">Date</th><th class="p-3">Status</th></tr></thead>
                        <tbody>
                            @forelse($approvals as $approval)
                                @php
                                    $badge = match ($approval->status) {
                                        'A', 'C' => 'bg-green-500 text-white', 'R' => 'bg-red-500 text-white',
                                        'D' => 'bg-blue-500 text-white', 'P' => 'bg-yellow-500 text-white',
                                        default => 'bg-gray-500 text-white',
                                    };
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="p-3">{{ number_format((float) $approval->aprv_leveling, 2) }}</td>
                                    <td class="p-3">{{ $approval->aprv_name ?: $approval->aprv_username }}</td>
                                    <td class="p-3">{{ $approval->aprv_dateafter ? \Carbon\Carbon::parse($approval->aprv_dateafter)->format('d M Y H:i:s') : '—' }}</td>
                                    <td class="p-3"><span class="{{ $badge }} inline-block rounded-full px-3 py-1 text-xs font-semibold">{{ ['P' => 'Waiting Approval', 'A' => 'Approved', 'C' => 'Completed', 'R' => 'Rejected', 'D' => 'Revised', 'X' => 'Cancelled'][$approval->status] ?? $approval->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada data approval.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div x-show="tab === 'comments'" x-cloak class="flex flex-col px-4" style="flex:1;min-height:0" role="tabpanel">
                    <div id="vendor-comment-list" class="space-y-3 overflow-y-auto p-4" style="flex:1;min-height:210px;max-height:300px" aria-live="polite"><p class="text-sm text-gray-500">Memuat pesan…</p></div>
                    <form id="vendor-comment-form" class="flex items-center gap-3 border-t border-gray-200 p-4 dark:border-gray-700">
                        <input id="vendor-comment-input" name="comment" type="text" required maxlength="500" aria-label="Write a comment" placeholder="Write a comment..." class="flex-1 rounded-lg border-gray-500 bg-gray-100 p-3 text-sm dark:bg-gray-700" style="min-width:0">
                        <button type="submit" id="vendor-comment-send" class="rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white disabled:opacity-50">Post 🚀</button>
                    </form>
                </div>
                <div x-show="tab === 'notes'" x-cloak class="space-y-4 p-5 text-sm" role="tabpanel">
                    <section class="vendor-note-box">
                        <h2 class="vendor-note-heading">Catatan Evaluasi</h2>
                        <p class="vendor-note-content">{{ $evaluation->vendor_perfomance_note ?: 'Belum ada catatan evaluasi.' }}</p>
                    </section>
                    <section class="vendor-note-box vendor-note-recommendation">
                        <h2 class="vendor-note-heading">Komentar / Rekomendasi</h2>
                        <p class="vendor-note-content">{{ $evaluation->template_vpe_aspect_comment ?: 'Belum ada komentar atau rekomendasi.' }}</p>
                    </section>
                </div>
            </section>
        </div>
        <section class="overflow-hidden rounded-xl bg-white dark:bg-gray-800">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                <div class="flex flex-wrap items-center gap-3"><h2 class="text-sm font-bold">Detail Penilaian Vendor — Aspek Penilaian (KPI)</h2>@include('pages.evaluasivendor.partials.score-guide-button')</div>
                <div class="vendor-kpi-summary" style="--kpi-accent:{{ $kpiAccent }};--kpi-tint:{{ $kpiTint }}" aria-label="Total Nilai KPI {{ number_format($kpiScore, 2) }}, {{ $evaluation->template_vpe_aspect_result }}">
                    <div>
                        <span class="vendor-kpi-label">Total Nilai KPI</span>
                        <strong class="vendor-kpi-value">{{ number_format($kpiScore, 2) }}</strong>
                    </div>
                    <div class="vendor-kpi-result">
                        <span class="vendor-kpi-label">Predikat</span>
                        <span class="vendor-kpi-predicate">{{ $evaluation->template_vpe_aspect_result ?: '—' }}</span>
                    </div>
                </div>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="table-layout:fixed;min-width:1100px;overflow-wrap:anywhere">
                    <colgroup><col style="width:3%"><col style="width:16%"><col style="width:5%"><col style="width:26%"><col style="width:12%"><col style="width:8%"><col style="width:23%"><col style="width:7%"></colgroup>
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700"><tr><th class="p-3">No</th><th class="p-3">Aspek / Subaspek</th><th class="p-3">Bobot %</th><th class="p-3">Kriteria Penilaian</th><th class="p-3">Skor</th><th class="p-3">Nilai Terbobot</th><th class="p-3">Catatan / Bukti Lapangan</th><th class="p-3">Attachment</th></tr></thead>
                    <tbody>
                        @php
                            $number = 0;
                        @endphp
                        @forelse($details->groupBy('template_vpe_aspect_id') as $aspectDetails)
                            <tr class="bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300"><th colspan="8" class="p-3">{{ $aspectDetails->first()->template_vpe_aspect_descr }}</th></tr>
                            @foreach($aspectDetails as $detail)
                                <tr class="border-b border-gray-100 align-top dark:border-gray-700">
                                    <td class="p-3">{{ ++$number }}</td><td class="p-3">{{ $detail->template_vpe_subaspect_descr }}</td>
                                    <td class="p-3">{{ number_format((float) $detail->template_vpe_subaspect_bobot, 2) }}</td>
                                    <td class="whitespace-pre-line p-3">{{ $detail->template_vpe_subaspect_kriteria }}</td>
                                    <td class="p-3">
                                        @php
                                            $detailScore = $detail->template_vpe_subaspect_score === null ? null : (int) $detail->template_vpe_subaspect_score;
                                            [$predicate, $predicateColor, $predicateBackground] = match ($detailScore) {
                                                5 => ['Sangat Baik', '#15803d', '#dcfce7'],
                                                4 => ['Baik', '#4338ca', '#e0e7ff'],
                                                3 => ['Cukup', '#a16207', '#fef3c7'],
                                                2 => ['Kurang', '#c2410c', '#ffedd5'],
                                                1 => ['Sangat Kurang', '#b91c1c', '#fee2e2'],
                                                default => [null, null, null],
                                            };
                                        @endphp
                                        <span class="vendor-detail-score"><strong>{{ $detailScore ?? '—' }}</strong>@if($predicate)<span class="vendor-detail-predicate" style="color:{{ $predicateColor }};background:{{ $predicateBackground }}">{{ $predicate }}</span>@endif</span>
                                    </td>
                                    <td class="p-3 font-semibold">{{ number_format((float) $detail->template_vpe_subaspect_nilai, 2) }}</td>
                                    <td class="whitespace-pre-line p-3">{{ $detail->template_vpe_subaspect_note ?: '—' }}</td>
                                    <td class="p-3">
                                        @php($detailAttachments = $attachments[$detail->id] ?? [])
                                        @if(count($detailAttachments))
                                            <button type="button" class="rounded border border-indigo-200 px-3 py-2 text-indigo-700" onclick="document.getElementById('kpi-files-{{ $detail->id }}').showModal()" aria-label="Lihat attachment">&#128206; {{ count($detailAttachments) }}</button>
                                            <dialog id="kpi-files-{{ $detail->id }}" class="w-full max-w-lg rounded-xl border-0 p-5 shadow-xl backdrop:bg-black/50">
                                                <h3 class="font-bold">Attachment KPI</h3><p class="mt-1 text-sm text-gray-500">{{ $detail->template_vpe_subaspect_descr }}</p>
                                                <ul class="my-4 space-y-2">@foreach($detailAttachments as $attachment)<li><a class="break-all text-indigo-600 underline" href="{{ $attachment['url'] }}" target="_blank" rel="noopener noreferrer">{{ $attachment['name'] }}</a></li>@endforeach</ul>
                                                <button type="button" class="rounded bg-indigo-600 px-4 py-2 text-white" onclick="this.closest('dialog').close()">Tutup</button>
                                            </dialog>
                                        @else — @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="8" class="p-6 text-center text-gray-500">Belum ada detail penilaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    @include('pages.evaluasivendor.partials.score-guide')
    <style>
        .vendor-detail-score{display:inline-flex;align-items:center;gap:8px;white-space:nowrap;font-variant-numeric:tabular-nums}
        .vendor-detail-predicate{display:inline-block;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:600;line-height:1.3}
        .vendor-note-box{border:1px solid #e2e8f0;border-left:3px solid #6366f1;border-radius:8px;padding:14px 16px;background:#f8fafc}
        .vendor-note-recommendation{border-left-color:#0d9488;background:#f0fdfa}
        .vendor-note-heading{margin:0;padding-bottom:8px;border-bottom:1px solid #e2e8f0;font-size:12px;font-weight:700;color:#334155}
        .vendor-note-content{margin:10px 0 0;white-space:pre-line;overflow-wrap:anywhere;line-height:1.65;color:#0f172a}
        .dark .vendor-note-box{background:#1f2937;border-color:#475569;border-left-color:#818cf8}.dark .vendor-note-recommendation{border-left-color:#2dd4bf}
        .dark .vendor-note-heading{color:#e2e8f0;border-bottom-color:#475569}.dark .vendor-note-content{color:#f1f5f9}
        .vendor-kpi-summary{display:flex;align-items:center;gap:12px;min-width:210px;padding:6px 12px;border:1px solid color-mix(in srgb,var(--kpi-accent) 18%,transparent);border-left:4px solid var(--kpi-accent);border-radius:10px;background:linear-gradient(120deg,var(--kpi-tint),#fff);box-shadow:0 4px 16px color-mix(in srgb,var(--kpi-accent) 8%,transparent)}
        .vendor-kpi-label{display:block;color:#64748b;font-size:10px;font-weight:600;letter-spacing:.04em;text-transform:uppercase}
        .vendor-kpi-value{display:block;margin-top:2px;color:var(--kpi-accent);font-size:24px;font-weight:800;line-height:1.1;letter-spacing:-.04em;font-variant-numeric:tabular-nums}
        .vendor-kpi-result{max-width:230px;border-left:1px solid color-mix(in srgb,var(--kpi-accent) 20%,transparent);padding-left:12px}
        .vendor-kpi-predicate{display:inline-block;margin-top:3px;border-radius:999px;padding:3px 9px;background:var(--kpi-accent);color:#fff;font-size:12px;font-weight:700;line-height:1.4}
        .dark .vendor-kpi-summary{background:linear-gradient(120deg,color-mix(in srgb,var(--kpi-accent) 16%,#111827),#1f2937);border-color:#374151;border-left-color:var(--kpi-accent)}
        .dark .vendor-kpi-label{color:#cbd5e1}.dark .vendor-kpi-value{color:color-mix(in srgb,var(--kpi-accent) 40%,#fff)}
        @media(max-width:640px){.vendor-kpi-summary{width:100%;min-width:0;gap:12px;padding:6px 10px}.vendor-kpi-value{font-size:24px}.vendor-kpi-result{padding-left:12px}}
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const commentsUrl = @json(route('evaluasivendor.comments', $hash));
            const commentList = document.getElementById('vendor-comment-list');
            const commentInput = document.getElementById('vendor-comment-input');
            const sendComment = document.getElementById('vendor-comment-send');
            const relativeTime = new Intl.RelativeTimeFormat('en', {numeric: 'auto'});
            function commentTime(value) {
                const time = new Date(value);
                if (Number.isNaN(time.getTime())) return '';
                const seconds = (time.getTime() - Date.now()) / 1000;
                const units = [[31536000, 'year'], [2592000, 'month'], [86400, 'day'], [3600, 'hour'], [60, 'minute'], [1, 'second']];
                const [divisor, unit] = units.find(([size]) => Math.abs(seconds) >= size) || units[units.length - 1];
                return relativeTime.format(Math.round(seconds / divisor), unit);
            }
            let commentsRequest = 0;
            async function loadComments() {
                const requestId = ++commentsRequest;
                try {
                    const response = await fetch(commentsUrl, {headers: {'Accept': 'application/json'}});
                    if (!response.ok) throw new Error('Pesan gagal dimuat. Buka kembali tab Comments untuk mencoba lagi.');
                    const result = await response.json();
                    if (requestId !== commentsRequest) return;
                    commentList.replaceChildren();
                    const count = document.getElementById('vendor-comment-count');
                    count.textContent = result.comments.length > 99 ? '99+' : result.comments.length;
                    count.hidden = result.comments.length === 0;
                    if (!result.comments.length) {
                        const empty = document.createElement('p');
                        empty.className = 'py-4 text-center text-sm text-gray-500';
                        empty.textContent = 'Belum ada komentar. Tulis pesan pertama Anda.';
                        commentList.append(empty);
                    }
                    result.comments.forEach(comment => {
                        const card = document.createElement('article');
                        card.className = 'rounded-lg bg-gray-100 p-3 text-sm dark:bg-gray-700';
                        const heading = document.createElement('p');
                        heading.className = 'text-xs';
                        const author = document.createElement('span');
                        author.className = 'font-semibold';
                        author.textContent = comment.username || comment.name;
                        const date = document.createElement('span');
                        date.className = 'text-gray-500 dark:text-gray-400';
                        const time = commentTime(comment.message_date || comment.created_at);
                        date.textContent = time ? ' (' + time + ')' : '';
                        heading.append(author, date);
                        const text = document.createElement('p');
                        text.className = 'mt-1 whitespace-pre-line break-words';
                        text.textContent = comment.message;
                        card.append(heading, text);
                        commentList.append(card);
                    });
                } catch (error) {
                    if (requestId === commentsRequest) commentList.textContent = error.message;
                }
            }
            document.addEventListener('load-vendor-comments', loadComments);
            document.getElementById('vendor-comment-form').addEventListener('submit', async function (event) {
                event.preventDefault();
                if (sendComment.disabled) return;
                const comment = commentInput.value.trim();
                if (!comment) {
                    await Swal.fire({icon: 'warning', title: 'Pesan wajib diisi'});
                    return;
                }
                sendComment.disabled = true;
                sendComment.textContent = 'Posting...';
                try {
                    const response = await fetch(commentsUrl, {method: 'POST', headers: {
                        'Accept': 'application/json', 'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }, body: JSON.stringify({comment})});
                    const result = await response.json().catch(() => ({}));
                    if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join('\n') : 'Pesan gagal dikirim. Silakan coba kembali.');
                    commentInput.value = '';
                    await loadComments();
                } catch (error) {
                    await Swal.fire({icon: 'error', title: 'Pesan belum terkirim', text: error.message});
                } finally {
                    sendComment.disabled = false;
                    sendComment.textContent = 'Post 🚀';
                }
            });
            loadComments();
            const actions = {approveBtn: ['approve', 'Approve'], reviseBtn: ['revise', 'Revise'], rejectBtn: ['reject', 'Reject']};
            let processing = false;
            Object.entries(actions).forEach(([id, [action, label]]) => {
                const button = document.getElementById(id);
                if (!button) return;
                button.addEventListener('click', async function () {
                    if (processing) return;
                    const confirmation = await Swal.fire({title: label + ' penilaian ini?',
                        input: action === 'approve' ? undefined : 'textarea', inputLabel: 'Alasan / komentar',
                        inputValidator: value => action !== 'approve' && !value.trim() ? 'Alasan wajib diisi.' : undefined,
                        showCancelButton: true, confirmButtonText: label, cancelButtonText: 'Batal'});
                    if (!confirmation.isConfirmed) return;
                    processing = true;
                    Object.keys(actions).forEach(key => {const el = document.getElementById(key); if (el) el.disabled = true;});
                    try {
                        const url = @json(route('evaluasivendor.approval', ['hash' => $hash, 'action' => '__ACTION__'])).replace('__ACTION__', action);
                        const response = await fetch(url, {method: 'POST', headers: {
                            'Accept': 'application/json', 'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }, body: JSON.stringify({comment: action === 'approve' ? null : confirmation.value})});
                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) {
                            const errors = result.errors ? Object.values(result.errors).flat().join('\n') : '';
                            throw new Error(errors || (response.status < 500 ? result.message : '') || 'Approval gagal diproses.');
                        }
                        window.location.assign(result.redirect);
                    } catch (error) {
                        await Swal.fire({icon: 'error', title: 'Proses belum berhasil', text: error.message});
                    } finally {
                        processing = false;
                        Object.keys(actions).forEach(key => {const el = document.getElementById(key); if (el) el.disabled = false;});
                    }
                });
            });
        });
    </script>
</x-app-layout>
