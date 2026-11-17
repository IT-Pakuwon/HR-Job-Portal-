<x-app-layout>
    @php
        $formatDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->translatedFormat('d F Y') : '—';
        $statusClass = match ($evaluation->status) {
            'C' => 'bg-green-100 text-green-700', 'R' => 'bg-red-100 text-red-700',
            'D' => 'bg-blue-100 text-blue-700', default => 'bg-yellow-100 text-yellow-700',
        };
    @endphp
    <div class="mx-auto w-full space-y-4 p-2">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Home</a><span>/</span>
                <a href="{{ route('evaluasivendor') }}" class="hover:text-indigo-600">Penilaian Vendor</a><span>/</span>
                <span class="font-semibold text-gray-800 dark:text-gray-100">Show Details</span>
            </nav>
            @if(in_array($evaluation->status, ['P', 'D']))
                <a href="{{ route('evaluasivendor.edit', $evaluation->id) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit Penilaian</a>
            @endif
        </div>
        @if(session('success'))<div role="status" class="rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <section class="overflow-hidden rounded-xl bg-white dark:bg-gray-800" style="min-height:360px">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-700 dark:bg-gray-700">
                    <h1 class="flex items-center gap-2 text-sm font-bold"><span class="rounded bg-purple-100 px-2 py-1 text-purple-700">ID</span>{{ $evaluation->vendor_perfomance_id }}</h1>
                    <span class="{{ $statusClass }} rounded-full px-4 py-1 text-xs font-semibold">{{ $statuses[$evaluation->status] ?? $evaluation->status }}</span>
                </header>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-5 text-sm sm:grid-cols-2">
                    @foreach([
                        'Company' => $evaluation->cpny_id,
                        'Department' => $evaluation->department_id,
                        'Tanggal Evaluasi' => $formatDate($evaluation->vendor_perfomance_date),
                        'Created User' => $evaluation->created_by,
                        'Sumber / No. Dokumen' => $evaluation->vendor_perfomance_type.' / '.$evaluation->ponbr,
                        'Vendor' => $evaluation->vendorname,
                        'Periode Mulai' => $formatDate($evaluation->vendor_perfomance_start_date),
                        'Periode Selesai' => $formatDate($evaluation->vendor_perfomance_end_date),
                    ] as $label => $value)
                        <div class="grid grid-cols-2 gap-2"><dt class="text-gray-500 dark:text-gray-400">{{ $label }}</dt><dd class="break-words font-medium">{{ $value ?: '—' }}</dd></div>
                    @endforeach
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700"><dt class="text-xs text-gray-500 dark:text-gray-400">Jenis Pekerjaan / Template</dt><dd class="mt-1 font-medium">{{ $evaluation->template_vpe_descr ?: '—' }}</dd></div>
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-700"><dt class="text-xs text-gray-500 dark:text-gray-400">Pekerjaan</dt><dd class="mt-1 whitespace-pre-line">{{ $evaluation->keperluan ?: '—' }}</dd></div>
                </dl>
            </section>
            <section x-data="{ tab: 'approval' }" class="overflow-hidden rounded-xl bg-white dark:bg-gray-800" style="min-height:360px">
                <div class="flex border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700" role="tablist" aria-label="Informasi penilaian">
                    <button type="button" @click="tab = 'approval'" :aria-selected="tab === 'approval'" :class="tab === 'approval' ? 'border-indigo-600 text-indigo-600' : 'border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-sm" role="tab">Approval Details</button>
                    <button type="button" @click="tab = 'notes'" :aria-selected="tab === 'notes'" :class="tab === 'notes' ? 'border-indigo-600 text-indigo-600' : 'border-transparent'" class="flex-1 border-b-2 px-4 py-4 text-sm" role="tab">Catatan / Rekomendasi</button>
                </div>
                <div x-show="tab === 'approval'" class="overflow-x-auto p-4" role="tabpanel">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b text-xs text-gray-500"><tr><th class="p-3">Level</th><th class="p-3">Approver</th><th class="p-3">Status</th><th class="p-3">Tanggal</th></tr></thead>
                        <tbody>
                            @forelse($approvals as $approval)
                                <tr class="border-b border-gray-100 dark:border-gray-700"><td class="p-3">{{ $approval->aprv_leveling }}</td><td class="p-3">{{ $approval->aprv_name ?: $approval->aprv_username }}</td><td class="p-3">{{ ['P' => 'Waiting Approval', 'A' => 'Approved', 'C' => 'Completed', 'R' => 'Rejected', 'D' => 'Revised', 'X' => 'Cancelled'][$approval->status] ?? $approval->status }}</td><td class="p-3">{{ $formatDate($approval->aprv_dateafter) }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="p-6 text-center text-gray-500">Belum ada data approval.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div x-show="tab === 'notes'" x-cloak class="space-y-4 p-5 text-sm" role="tabpanel">
                    <div><h2 class="font-semibold text-gray-500">Catatan Evaluasi</h2><p class="mt-2 whitespace-pre-line">{{ $evaluation->vendor_perfomance_note ?: '—' }}</p></div>
                    <div><h2 class="font-semibold text-gray-500">Komentar / Rekomendasi</h2><p class="mt-2 whitespace-pre-line">{{ $evaluation->template_vpe_aspect_comment ?: '—' }}</p></div>
                </div>
            </section>
        </div>
        <section class="overflow-hidden rounded-xl bg-white dark:bg-gray-800">
            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-5 py-3 dark:border-gray-700">
                <h2 class="text-sm font-bold">Detail Penilaian Vendor — Aspek Penilaian (KPI)</h2>
                <div class="flex items-center gap-3 text-sm"><span>Total Nilai KPI</span><strong class="text-lg text-indigo-600">{{ number_format((float) $evaluation->template_vpe_aspect_grandtotal_score, 2) }}</strong><span class="rounded-full bg-indigo-50 px-3 py-1 font-semibold text-indigo-700">{{ $evaluation->template_vpe_aspect_result ?: '—' }}</span></div>
            </header>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="table-layout:fixed;min-width:1100px;overflow-wrap:anywhere">
                    <colgroup><col style="width:4%"><col style="width:17%"><col style="width:6%"><col style="width:29%"><col style="width:7%"><col style="width:9%"><col style="width:28%"></colgroup>
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-700"><tr><th class="p-3">No</th><th class="p-3">Aspek / Subaspek</th><th class="p-3">Bobot %</th><th class="p-3">Kriteria Penilaian</th><th class="p-3">Skor</th><th class="p-3">Nilai Terbobot</th><th class="p-3">Catatan / Bukti Lapangan</th></tr></thead>
                    <tbody>
                        @php($number = 0)
                        @forelse($details->groupBy('template_vpe_aspect_id') as $aspectDetails)
                            <tr class="bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300"><th colspan="7" class="p-3">{{ $aspectDetails->first()->template_vpe_aspect_descr }}</th></tr>
                            @foreach($aspectDetails as $detail)
                                <tr class="border-b border-gray-100 align-top dark:border-gray-700">
                                    <td class="p-3">{{ ++$number }}</td><td class="p-3">{{ $detail->template_vpe_subaspect_descr }}</td>
                                    <td class="p-3">{{ number_format((float) $detail->template_vpe_subaspect_bobot, 2) }}</td>
                                    <td class="whitespace-pre-line p-3">{{ $detail->template_vpe_subaspect_kriteria }}</td>
                                    <td class="p-3">{{ $detail->template_vpe_subaspect_score === null ? '—' : (int) $detail->template_vpe_subaspect_score }}</td>
                                    <td class="p-3 font-semibold">{{ number_format((float) $detail->template_vpe_subaspect_nilai, 2) }}</td>
                                    <td class="whitespace-pre-line p-3">{{ $detail->template_vpe_subaspect_note ?: '—' }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr><td colspan="7" class="p-6 text-center text-gray-500">Belum ada detail penilaian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
