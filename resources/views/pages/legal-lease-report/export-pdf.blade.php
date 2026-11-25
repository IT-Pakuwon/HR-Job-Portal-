<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
@page {
    margin: 16mm 20mm 14mm 20mm;
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8.5px;
    color: #1e293b;
    background: #ffffff;
}

.rpt-header {
    background-color: #1e3a8a;
    padding: 16px 20px 14px;
}
.rpt-header h1 {
    font-size: 19px;
    font-weight: bold;
    color: #ffffff;
    margin-bottom: 4px;
    letter-spacing: -0.02em;
}
.rpt-header .meta {
    font-size: 7.5px;
    color: #bfdbfe;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px solid #3b82f6;
}
.rpt-header .meta span { margin-right: 20px; }

.rpt-header-stripe {
    height: 4px;
    background-color: #3b82f6;
}

.page-wrap { padding: 16px 22px 0; }

.section { margin-top: 16px; }

.sec-head {
    font-size: 7.5px;
    font-weight: bold;
    color: #1e3a8a;
    background-color: #eff6ff;
    padding: 6px 12px 6px 14px;
    border-left: 4px solid #3b82f6;
    border-bottom: 1px solid #bfdbfe;
    margin-bottom: 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.sum-tbl { width: 100%; border-collapse: collapse; }
.sum-tbl td {
    width: 20%;
    padding: 13px 12px 11px;
    text-align: center;
    border: 1px solid #dbeafe;
    vertical-align: top;
    background-color: #fafbff;
}
.s-lbl {
    font-size: 6px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 7px;
    font-weight: bold;
}
.s-val {
    font-size: 16px;
    font-weight: bold;
    color: #0f172a;
    line-height: 1.1;
}

.dt { width: 100%; border-collapse: collapse; }
.dt thead th {
    background-color: #1e3a8a;
    color: #dbeafe;
    font-weight: bold;
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 7px 10px;
    text-align: left;
}
.dt tbody td {
    padding: 5.5px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 7.5px;
    color: #1e293b;
}
.dt tbody .ev { background-color: #f8faff; }

.status-pill {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 8px;
    font-size: 6.5px;
    font-weight: bold;
    background-color: #d1fae5;
    color: #047857;
}
.status-pill.op { background-color: #fed7aa; color: #b45309; }

.rpt-footer {
    display: table;
    width: 100%;
    border-top: 2px solid #3b82f6;
    padding-top: 7px;
    margin-top: 18px;
}
.rpt-footer .ft-l {
    display: table-cell;
    text-align: left;
    font-size: 6.5px;
    font-weight: bold;
    color: #3b82f6;
}
.rpt-footer .ft-r {
    display: table-cell;
    text-align: right;
    font-size: 6.5px;
    color: #94a3b8;
}
</style>
</head>
<body>

@php
    $typeLabels = ['PSM' => 'PSM', 'OLA' => 'OLA', 'ADDENDUM' => 'Addendum', 'OTHERS' => 'Others', 'FU' => 'Agreement FU'];
    $typeLabel = $typeLabels[$type ?? ''] ?? 'All Types';
    $s = $summary;
@endphp

<div class="rpt-header">
    <h1>Legal Lease Report — {{ $typeLabel }}</h1>
    <div class="meta">
        <span><strong>Period:</strong> {{ $dateFrom }} &ndash; {{ $dateTo }}</span>
        <span><strong>Company:</strong> {{ $cpnyId ?: 'All Companies' }}</span>
        <span><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</span>
    </div>
</div>
<div class="rpt-header-stripe"></div>
<div class="page-wrap">

<div class="section">
    <div class="sec-head">Summary</div>
    <table class="sum-tbl">
        <tr>
            <td>
                <div class="s-lbl">Total Agreements</div>
                <div class="s-val">{{ $s['total'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Completed</div>
                <div class="s-val" style="color:#059669">{{ $s['completed'] }}</div>
            </td>
            <td>
                <div class="s-lbl">In Progress</div>
                <div class="s-val" style="color:#d97706">{{ $s['on_progress'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Completion Rate</div>
                <div class="s-val" style="color:#3193da">{{ $s['completion_rate'] }}%</div>
            </td>
            <td>
                <div class="s-lbl">Incomplete Documents</div>
                <div class="s-val" style="color:#dc2626">{{ $s['incomplete_docs'] }}</div>
            </td>
        </tr>
    </table>
</div>

@php
    $cards = $cards ?? [];
    $byType = $byType ?? [];
    $memo = $memo ?? [];
    $sent = $sent ?? [];
    $workload = $workload ?? [];
    $delay = $delay ?? [];
    $matrix = function (array $x) {
        $series = $x['series'] ?? [];
        $rows = [];
        foreach (($x['categories'] ?? []) as $i => $label) {
            $vals = array_map(fn ($sr) => (int) ($sr['data'][$i] ?? 0), $series);
            $rows[] = ['label' => $label, 'vals' => $vals, 'total' => array_sum($vals)];
        }
        return [array_column($series, 'name'), $rows];
    };
    [$typeCols, $typeRows] = $matrix($byType);
    [$picCols, $picRows] = $matrix($workload);
@endphp

<div class="section">
    <div class="sec-head">Current State</div>
    <table class="sum-tbl">
        <tr>
            <td style="width:33%"><div class="s-lbl">PSM / OLA / Addendum / Others — Active or Draft</div><div class="s-val">{{ $cards['active_draft'] ?? 0 }}</div></td>
            <td style="width:33%"><div class="s-lbl">Follow Up — Active</div><div class="s-val">{{ $cards['followup_active'] ?? 0 }}</div></td>
            <td style="width:34%"><div class="s-lbl">Without complete tenant documents</div><div class="s-val" style="color:#dc2626">{{ $cards['incomplete_docs'] ?? 0 }}</div></td>
        </tr>
    </table>
    @if(!empty($cards['highlights']))
        <table class="dt">
            <thead><tr><th>Key Highlights</th></tr></thead>
            <tbody>
                @foreach($cards['highlights'] as $i => $h)
                    <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}"><td>{{ $h }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<div class="section">
    <div class="sec-head">Agreement Type by Company</div>
    <table class="dt">
        <thead><tr><th>Type</th>@foreach($typeCols as $c)<th style="text-align:right">{{ $c }}</th>@endforeach<th style="text-align:right">Total</th></tr></thead>
        <tbody>
            @forelse($typeRows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}"><td>{{ $r['label'] }}</td>@foreach($r['vals'] as $v)<td style="text-align:right">{{ $v }}</td>@endforeach<td style="text-align:right"><strong>{{ $r['total'] }}</strong></td></tr>
            @empty
                <tr><td colspan="{{ count($typeCols) + 2 }}" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="sec-head">Memo &amp; LOI by Property</div>
    <table class="dt">
        <thead><tr><th>Document</th><th>Property</th><th style="text-align:right">Received</th><th style="text-align:right">Pending</th><th>Received by company</th></tr></thead>
        <tbody>
            @php $n = 0; @endphp
            @foreach(['new_loi_memo' => 'New LOI & Memo (PSM / OLA)', 'addendum_memo' => 'Change Memo (Addendum)'] as $key => $label)
                @foreach(($memo[$key]['by_property'] ?? []) as $p)
                    <tr class="{{ $n++ % 2 === 1 ? 'ev' : '' }}">
                        <td>{{ $label }}</td><td>{{ $p['property'] }}</td>
                        <td style="text-align:right">{{ $p['received'] }}</td><td style="text-align:right">{{ $p['pending'] }}</td>
                        <td>{{ collect($p['by_company'] ?? [])->map(fn ($c, $k) => $k.': '.$c)->implode(', ') }}</td>
                    </tr>
                @endforeach
                <tr class="{{ $n++ % 2 === 1 ? 'ev' : '' }}">
                    <td><strong>{{ $label }}</strong></td><td><strong>Total</strong></td>
                    <td style="text-align:right"><strong>{{ $memo[$key]['received'] ?? 0 }}</strong></td>
                    <td style="text-align:right"><strong>{{ $memo[$key]['pending'] ?? 0 }}</strong></td><td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <div class="sec-head">Follow-up Letters (tenants)</div>
    <table class="dt">
        <thead><tr><th>Follow Up Type</th><th style="text-align:right">Sent</th><th style="text-align:right">Surat 1</th><th style="text-align:right">Surat 2</th><th style="text-align:right">Escalation</th></tr></thead>
        <tbody>
            @forelse(($sent['rows'] ?? []) as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}"><td>{{ $r['label'] }}</td><td style="text-align:right">{{ $r['sent'] }}</td><td style="text-align:right">{{ $r['surat1'] }}</td><td style="text-align:right">{{ $r['surat2'] }}</td><td style="text-align:right">{{ $r['escalation'] }}</td></tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
            @if(!empty($sent['total']))
                <tr><td><strong>Total</strong></td><td style="text-align:right"><strong>{{ $sent['total']['sent'] }}</strong></td><td style="text-align:right"><strong>{{ $sent['total']['surat1'] }}</strong></td><td style="text-align:right"><strong>{{ $sent['total']['surat2'] }}</strong></td><td style="text-align:right"><strong>{{ $sent['total']['escalation'] }}</strong></td></tr>
            @endif
        </tbody>
    </table>
</div>

<div class="section">
    <div class="sec-head">PIC Workload (open documents)</div>
    <table class="dt">
        <thead><tr><th>PIC Legal</th>@foreach($picCols as $c)<th style="text-align:right">{{ $c }}</th>@endforeach<th style="text-align:right">Total</th></tr></thead>
        <tbody>
            @forelse($picRows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}"><td>{{ $r['label'] }}</td>@foreach($r['vals'] as $v)<td style="text-align:right">{{ $v }}</td>@endforeach<td style="text-align:right"><strong>{{ $r['total'] }}</strong></td></tr>
            @empty
                <tr><td colspan="{{ count($picCols) + 2 }}" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="sec-head">Delay Analysis ({{ count($delay) }})</div>
    <table class="dt">
        <thead><tr><th>Agreement</th><th>Tenant</th><th>PIC Legal</th><th style="text-align:right">Age</th><th>Cause</th><th>Detail</th></tr></thead>
        <tbody>
            @forelse($delay as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}">
                    <td>{{ $r['docid'] }}<br><span style="color:#94a3b8">{{ $r['type'] }} · {{ $r['company'] }}</span></td>
                    <td>{{ $r['tenant'] }}</td>
                    <td>{{ $r['pic'] }}</td>
                    <td style="text-align:right">{{ $r['age'] }}d</td>
                    <td>{{ $r['cause'] }}</td>
                    <td>{!! nl2br(e($r['detail'])) !!}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="section">
    <div class="sec-head">Agreement List ({{ count($tableRows) }})</div>
    <table class="dt">
        <thead>
            <tr>
                <th>Agreement</th>
                <th>Date</th>
                <th>Type</th>
                <th>Company</th>
                <th>Tenant</th>
                <th>Unit</th>
                <th>PIC Legal</th>
                <th>Docs</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tableRows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}">
                    <td>{{ $r['docid'] }}@if($r['no_psm'])<br><span style="color:#94a3b8">{{ $r['no_psm'] }}</span>@endif</td>
                    <td>{{ $r['date'] }}</td>
                    <td>{{ $r['category'] }}</td>
                    <td>{{ $r['company'] }}</td>
                    <td>{{ $r['tenant'] }}</td>
                    <td>{{ $r['unit'] }}</td>
                    <td>{{ $r['pic'] }}</td>
                    <td>{{ $r['docs'] }}</td>
                    <td>
                        <span class="status-pill {{ $r['status'] === 'COMPLETED' ? '' : 'op' }}">{{ $r['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="rpt-footer">
    <div class="ft-l">Legal Lease Report</div>
    <div class="ft-r">Generated {{ now()->format('d M Y, H:i') }}</div>
</div>

</div>
</body>
</html>
