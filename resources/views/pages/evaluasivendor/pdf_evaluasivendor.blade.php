<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Evaluasi Vendor {{ $evaluation->vendor_perfomance_id }}</title>
    <style>
        @page{margin:12mm 10mm 14mm}
        body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#111;line-height:1.3}
        h1{font-size:13px;text-align:center;margin:0 0 12px;text-transform:uppercase}
        table{width:100%;border-collapse:collapse;table-layout:fixed}
        th,td{padding:4px;vertical-align:top;overflow-wrap:break-word}
        .meta{margin-bottom:10px}.meta td{padding:3px 4px}.label{width:22%;font-weight:bold}
        .kpi th,.kpi td,.guide th,.guide td,.approval th,.approval td{border:1px solid #222}
        .kpi thead{display:table-header-group}.kpi thead th{background:#eaeef4;text-align:center;font-size:8px}
        .kpi tr,.approval tr{page-break-inside:avoid}.aspect{background:#e3f3ff;font-weight:bold}
        .number{text-align:center}.total{font-weight:bold;background:#e3f3ff}.text{white-space:pre-line;word-wrap:break-word}
        .notes{margin-top:10px}.notes td{border:1px solid #999;padding:6px}.notes strong{display:block;margin-bottom:3px}
        .guide{margin-top:10px;font-size:8px}.guide th{background:#f3f4f6;text-align:left}.guide tr{page-break-inside:avoid}
        .approval{margin-top:14px;font-size:8px}.approval th{text-align:left;background:#f7f7f7}.approval td{height:42px}
        .sig-name{font-weight:bold;word-wrap:break-word}.blue{color:#174a8b}.red{color:#b91c1c}.orange{color:#9a5700}
        .formula{margin:4px 0;color:#555;font-size:8px}
    </style>
</head>
<body>
    @php
        $date = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y') : '—';
        $timestamp = fn ($value) => $value ? \Carbon\Carbon::parse($value)->format('d M Y H:i') : '';
        // Standard printed legend from the evaluation forms; independent of import files.
        $guide = [
            'scores' => [
                ['label' => '5 (Sangat Baik)', 'value' => 120, 'description' => 'Melebihi ekspektasi, kualitas di atas standar, tidak ada temuan, pekerjaan selesai lebih cepat dari target.'],
                ['label' => '4 (Baik)', 'value' => 100, 'description' => 'Memenuhi seluruh persyaratan kontrak dan spesifikasi dengan baik.'],
                ['label' => '3 (Cukup)', 'value' => 80, 'description' => 'Memenuhi standar minimum namun terdapat temuan minor atau perbaikan kecil.'],
                ['label' => '2 (Kurang)', 'value' => 60, 'description' => 'Terdapat beberapa ketidaksesuaian, keterlambatan, atau kualitas pekerjaan di bawah target yang memerlukan tindakan korektif.'],
                ['label' => '1 (Sangat Kurang)', 'value' => 40, 'description' => 'Gagal memenuhi kontrak, kualitas pekerjaan tidak dapat diterima, terdapat temuan mayor/berulang, atau pelanggaran K3.'],
            ],
            'result_categories' => [
                ['range' => '≥ 90', 'label' => 'Sangat Baik'],
                ['range' => '80 - <90', 'label' => 'Baik'],
                ['range' => '70 - <80', 'label' => 'Cukup'],
                ['range' => '60 - <70', 'label' => 'Kurang'],
                ['range' => '< 60', 'label' => $evaluation->template_vpe_id === 'VPE-SLIDING-DOOR' ? 'Buruk / Perlu Evaluasi Khusus' : 'Sangat Kurang/Perlu Evaluasi Khusus'],
            ],
        ];
    @endphp
    <h1>Form Evaluasi Kinerja Vendor<br>{{ $evaluation->template_vpe_descr }}</h1>
    <table class="meta">
        <tr><td class="label">No. Evaluasi</td><td>{{ $evaluation->vendor_perfomance_id }}</td><td class="label">Tanggal Evaluasi</td><td>{{ $date($evaluation->vendor_perfomance_date) }}</td></tr>
        <tr><td class="label">Business Unit</td><td>{{ $evaluation->cpny_id }}</td><td class="label">Department</td><td>{{ $evaluation->department_id }}</td></tr>
        <tr><td class="label">Nama Vendor</td><td colspan="3">{{ $evaluation->vendorname }}</td></tr>
        <tr><td class="label">Jenis Pekerjaan</td><td colspan="3">{{ $evaluation->template_vpe_descr }}</td></tr>
        <tr><td class="label">{{ $evaluation->vendor_perfomance_type === 'SPK' ? 'SPK / PO No' : 'Kontrak No' }}</td><td colspan="3">{{ $evaluation->ponbr }}</td></tr>
        <tr><td class="label">Periode Evaluasi</td><td colspan="3">{{ $date($evaluation->vendor_perfomance_start_date) }} — {{ $date($evaluation->vendor_perfomance_end_date) }}</td></tr>
        <tr><td class="label">Pekerjaan</td><td colspan="3" class="text">{{ $evaluation->keperluan }}</td></tr>
    </table>
    <table class="kpi">
        <thead><tr>
            <th style="width:3%">No</th>
            <th style="width:19%">Aspek Penilaian (KPI)</th>
            <th style="width:6%">Bobot (%)</th>
            <th style="width:43%">Kriteria Penilaian</th>
            <th style="width:5%">Skor (1–5)</th>
            <th style="width:7%">Skor Terbobot</th>
            <th style="width:17%">Catatan / Bukti Lapangan</th>
        </tr></thead>
        <tbody>
            @forelse($details->groupBy('template_vpe_aspect_id') as $group)
                <tr class="aspect"><td class="number">{{ $loop->iteration }}</td><td>{{ $group->first()->template_vpe_aspect_descr }}</td><td class="number">{{ number_format($group->sum('template_vpe_subaspect_bobot'), 2) }}</td><td colspan="4"></td></tr>
                @foreach($group as $detail)
                    <tr><td></td><td>{{ $detail->template_vpe_subaspect_descr }}</td><td class="number">{{ number_format((float) $detail->template_vpe_subaspect_bobot, 2) }}</td><td class="text">{{ $detail->template_vpe_subaspect_kriteria }}</td><td class="number">{{ $detail->template_vpe_subaspect_score === null ? '—' : (int) $detail->template_vpe_subaspect_score }}</td><td class="number">{{ number_format((float) $detail->template_vpe_subaspect_nilai, 2) }}</td><td class="text">{{ $detail->template_vpe_subaspect_note }}</td></tr>
                @endforeach
            @empty
                <tr><td colspan="7" class="number">Belum ada detail penilaian.</td></tr>
            @endforelse
            <tr class="total"><td colspan="2">TOTAL NILAI KPI</td><td class="number">{{ number_format($details->sum('template_vpe_subaspect_bobot'), 2) }}</td><td colspan="2"></td><td class="number">{{ number_format((float) $evaluation->template_vpe_aspect_grandtotal_score, 2) }}</td><td>{{ $evaluation->template_vpe_aspect_result }}</td></tr>
        </tbody>
    </table>
    <p class="formula">Nilai terbobot = bobot (%) × nilai konversi skor / 100. Skor 1=40, 2=60, 3=80, 4=100, 5=120.</p>
    <table class="notes"><tr><td><strong>Catatan Evaluasi</strong><div class="text">{{ $evaluation->vendor_perfomance_note ?: '—' }}</div></td><td><strong>Komentar / Rekomendasi</strong><div class="text">{{ $evaluation->template_vpe_aspect_comment ?: '—' }}</div></td></tr></table>
    @if($guide)
        <table class="guide">
            <thead><tr><th style="width:22%">Panduan Skor (1–5)</th><th>Keterangan</th><th style="width:10%">Nilai</th></tr></thead>
            <tbody>@foreach($guide['scores'] as $score)<tr><td>{{ $score['label'] }}</td><td>{{ $score['description'] }}</td><td class="number">{{ $score['value'] }}</td></tr>@endforeach</tbody>
        </table>
        <table class="guide"><thead><tr><th style="width:22%">Kategori Hasil Akhir Vendor</th><th>Predikat</th></tr></thead><tbody>@foreach($guide['result_categories'] as $category)<tr><td>{{ $category['range'] }}</td><td>{{ $category['label'] }}</td></tr>@endforeach</tbody></table>
    @endif
    @php
        $columns = $approvals->count() > 5 ? 4 : 3;
        $chunks = $approvals->values()->chunk($columns);
        $index = 1;
    @endphp
    <table class="approval">
        <thead><tr><th colspan="{{ $columns + 1 }}">Status: {{ $statuses[$evaluation->status] ?? $evaluation->status }}</th></tr></thead>
        <tbody>
            @forelse($chunks as $chunk)
                <tr>
                    @if($loop->first)<td rowspan="{{ $chunks->count() }}" style="width:24%"><div class="sig-name">{{ $creatorName }}</div><div class="blue">Created</div><div>{{ $timestamp($evaluation->created_at) }}</div></td>@endif
                    @foreach($chunk as $approval)
                        @php
                            $label = ['A' => 'Approved', 'C' => 'Completed', 'P' => 'Waiting', 'R' => 'Rejected', 'D' => 'Revised'][$approval->status] ?? $approval->status;
                            $color = match ($approval->status) {'A', 'C' => 'blue', 'P' => 'orange', default => 'red'};
                        @endphp
                        <td><div class="sig-name">{{ $index++ }}. {{ $approval->aprv_name ?: $approval->aprv_username }}</div><div class="{{ $color }}">{{ $label }}</div><div>{{ $timestamp($approval->aprv_dateafter) }}</div></td>
                    @endforeach
                    @for($i = $chunk->count(); $i < $columns; $i++)<td>&nbsp;</td>@endfor
                </tr>
            @empty
                <tr><td colspan="{{ $columns + 1 }}"><div class="sig-name">{{ $creatorName }}</div><div class="blue">Created</div><div>{{ $timestamp($evaluation->created_at) }}</div><div>Belum ada approval.</div></td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
