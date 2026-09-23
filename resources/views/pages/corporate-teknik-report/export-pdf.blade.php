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
    width: 25%;
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
    $typeLabel = $ticketType === 'BA' ? 'Berita Acara' : 'Support Ticket';
    $s = $summary;
@endphp

<div class="rpt-header">
    <h1>Corporate Teknik Report — {{ $typeLabel }}</h1>
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
                <div class="s-lbl">Total Ticket</div>
                <div class="s-val">{{ $s['total_ticket'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Completed</div>
                <div class="s-val" style="color:#059669">{{ $s['completed'] }}</div>
            </td>
            <td>
                <div class="s-lbl">On Progress</div>
                <div class="s-val" style="color:#d97706">{{ $s['on_progress'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Completion Rate</div>
                <div class="s-val" style="color:#7c3aed">{{ $s['completion_rate'] }}%</div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="sec-head">Ticket List ({{ count($tableRows) }})</div>
    <table class="dt">
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Date</th>
                <th>Unit</th>
                <th>Category</th>
                <th>Equipment/System</th>
                <th>Issue</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tableRows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}">
                    <td>{{ $r['ticketid'] }}</td>
                    <td>{{ $r['date'] }}</td>
                    <td>{{ $r['unit'] }}</td>
                    <td>{{ $r['category'] }}</td>
                    <td>{{ $r['equipment_system'] }}</td>
                    <td>{{ $r['issue'] }}</td>
                    <td>
                        <span class="status-pill {{ $r['status'] === 'COMPLETED' ? '' : 'op' }}">{{ $r['status'] }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="rpt-footer">
    <div class="ft-l">Corporate Teknik Report</div>
    <div class="ft-r">Generated {{ now()->format('d M Y, H:i') }}</div>
</div>

</div>
</body>
</html>
