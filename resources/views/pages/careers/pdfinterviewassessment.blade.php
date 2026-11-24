<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Formulir Penilaian Wawancara - {{ $career->docid }}</title>
    <style>
        @page { margin: 25px 26px 32px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #18212f; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .header td { vertical-align: middle; }
        .logo { width: 39px; max-height: 39px; }
        .company { font-size: 11px; font-weight: bold; }
        .docid { text-align: right; color: #526172; font-size: 8px; }
        .title { text-align: center; margin: 6px 0 12px; }
        .title h1 { margin: 0; font-size: 11px; }
        .subtitle { margin-top: 3px; font-size: 8px; }
        table.matrix, table.info { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .info td { padding: 4px 6px; border: 1px solid #66717c; vertical-align: top; }
        .label { font-size: 7px; text-transform: uppercase; color: #526172; }
        .value { font-weight: bold; margin-top: 3px; }
        .matrix { margin-top: 10px; }
        .matrix th, .matrix td { border: 1px solid #7d8793; padding: 4px; vertical-align: top; overflow-wrap: break-word; }
        .matrix th { background: #e9edf3; text-align: center; font-size: 7px; }
        .matrix tr { page-break-inside: avoid; }
        .criterion { width: 16%; font-weight: bold; }
        .score-col { width: 5%; text-align: center; font-weight: bold; font-size: 10px; }
        .option { font-size: 6.4px; line-height: 1.32; }
        .mark { margin-top: 4px; text-align: center; font-size: 8px; font-weight: bold; }
        .total { margin-top: 8px; text-align: right; font-weight: bold; }
        .standards { margin-top: 6px; font-size: 7px; color: #4b5563; }
        .section-title { margin: 16px 0 6px; padding-bottom: 4px; font-size: 10px; border-bottom: 1px solid #aeb8c4; }
        .review { border: 1px solid #aeb8c4; margin-bottom: 8px; padding: 7px; page-break-inside: avoid; }
        .review-head { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .review-head td { font-weight: bold; }
        .review-head td:last-child { text-align: right; }
        .review-table { width: 100%; table-layout: fixed; border-collapse: collapse; }
        .review-table td { width: 33%; padding: 3px 7px 3px 0; vertical-align: top; overflow-wrap: break-word; }
        .review-value { white-space: pre-wrap; line-height: 1.45; margin-top: 3px; }
        .empty { color: #697586; padding: 6px 0; }
    </style>
</head>
<body>
    <table class="header"><tr>
        <td style="width: 46px;"><img class="logo" src="{{ public_path('logo/logo.png') }}" alt="Pakuwon"></td>
        <td class="company">{{ $companyName ?: $career->cpnyid }}</td>
        <td class="docid">{{ $career->docid }}<br>Interview {{ $type === 'hc' ? 'HC' : 'User' }}</td>
    </tr></table>

    <div class="title">
        <h1>FORMULIR PENILAIAN WAWANCARA</h1>
        <div class="subtitle">INTERVIEW ASSESSMENT FORM &mdash; {{ strtoupper($type) }}</div>
    </div>

    <table class="info">
        <tr>
            <td><div class="label">Nama Pelamar / Applicant's Name</div><div class="value">{{ $career->fullname ?: '—' }}</div></td>
            <td><div class="label">Posisi yang Diinginkan / Position Applied For</div><div class="value">{{ trim(($career->job_title ?? '') . ' ' . ($career->job_level ?? '')) ?: '—' }}</div></td>
        </tr>
        <tr>
            <td><div class="label">Tanggal Wawancara / Date of Interview</div><div class="value">{{ $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->format('d-m-Y') : '—' }}</div></td>
            <td><div class="label">Jam / Time</div><div class="value">{{ $assessment->assessment_date ? \Carbon\Carbon::parse($assessment->assessment_date)->format('H:i') : '—' }}</div></td>
        </tr>
        <tr><td colspan="2"><div class="label">Pewawancara / Interviewer</div><div class="value">{{ $names[$assessment->user] ?? ($assessment->user ?: '—') }}</div></td></tr>
    </table>

    <table class="matrix">
        <thead><tr><th class="criterion">KRITERIA</th>@foreach(range(0, 4) as $score)<th>SKOR {{ $score }}</th>@endforeach<th class="score-col">NILAI</th></tr></thead>
        <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td class="criterion">{{ $group['label'] ?: '—' }}</td>
                    @foreach (range(0, 4) as $score)
                        @php($option = $group['options']->get((string) $score))
                        <td class="option">
                            {{ $option->assessment_descr ?? '—' }}
                            @if($option && $group['selected'] !== null && (string) $group['selected'] === (string) $score)
                                <div class="mark">[ X ]</div>
                            @else
                                <div class="mark">[ &nbsp; ]</div>
                            @endif
                        </td>
                    @endforeach
                    <td class="score-col">{{ $group['selected'] !== null ? $group['selected'] : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">Kriteria penilaian belum tersedia.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="total">JUMLAH NILAI: {{ $assessment->assessment_date ? $assessment->total_assessment_score_value : '—' }} / 28</div>
    <div class="standards"><strong>STANDAR SCORING:</strong> SUITABLE 21–28 (Manager/Supervisor), 20–25 (Staff) &nbsp; | &nbsp; CONSIDER 15–19 &nbsp; | &nbsp; NOT SUITABLE 0–14</div>

    <h2 class="section-title">FINAL ASSESSMENT RESULT</h2>
    @forelse($results as $result)
        <div class="review">
            <table class="review-head"><tr><td>{{ $names[$result->created_user] ?? $result->created_user }}</td><td>{{ $result->assessment_result ?: '—' }}</td></tr></table>
            <table class="review-table"><tr>
                <td><div class="label">Strengths</div><div class="review-value">{{ $result->assessment_strengths ?: '—' }}</div></td>
                <td><div class="label">Weaknesses</div><div class="review-value">{{ $result->assessment_weaknesses ?: '—' }}</div></td>
                <td><div class="label">Comment</div><div class="review-value">{{ $result->assessment_comment ?: '—' }}</div></td>
            </tr></table>
        </div>
    @empty
        <p class="empty">Belum ada hasil akhir dari reviewer.</p>
    @endforelse
</body>
</html>
