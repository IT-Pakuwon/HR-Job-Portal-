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
    font-size: 15px;
    font-weight: bold;
    color: #0f172a;
    line-height: 1.1;
}

.two-col { display: table; width: 100%; table-layout: fixed; border-spacing: 10px 0; }
.two-col .col { display: table-cell; width: 50%; vertical-align: top; }

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
.dt .num { text-align: right; }

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

@php $s = $summary; $qf = $quotaFunnel; @endphp

<div class="rpt-header">
    <h1>Training Report</h1>
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
                <div class="s-lbl">Total Attendance</div>
                <div class="s-val">{{ $s['total_attendance'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Avg. Satisfaction</div>
                <div class="s-val" style="color:#059669">{{ $s['avg_satisfaction'] ?? '–' }} / 5</div>
            </td>
            <td>
                <div class="s-lbl">Avg. Stars</div>
                <div class="s-val" style="color:#d97706">{{ $s['avg_stars'] }} / 5</div>
            </td>
            <td>
                <div class="s-lbl">Total Sessions</div>
                <div class="s-val" style="color:#7c3aed">{{ $s['total_sessions'] }}</div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="sec-head">Capacity &ndash; Quota vs Registered vs Attended</div>
    <table class="sum-tbl">
        <tr>
            <td>
                <div class="s-lbl">Quota</div>
                <div class="s-val">{{ $qf['quota'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Registered</div>
                <div class="s-val" style="color:#7c3aed">{{ $qf['registered'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Attended</div>
                <div class="s-val" style="color:#d97706">{{ $qf['attended'] }}</div>
            </td>
            <td>
                <div class="s-lbl">Fill Rate</div>
                <div class="s-val" style="color:#0891b2">{{ $qf['fill_rate'] }}%</div>
            </td>
            <td>
                <div class="s-lbl">No-Show Rate</div>
                <div class="s-val" style="color:#dc2626">{{ $qf['no_show_rate'] }}%</div>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="two-col">
        <div class="col">
            <div class="sec-head">By Department</div>
            <table class="dt">
                <thead><tr><th>Department</th><th class="num">Attendance</th></tr></thead>
                <tbody>
                    @forelse($byDepartment as $name => $count)
                        <tr class="{{ $loop->index % 2 === 1 ? 'ev' : '' }}">
                            <td>{{ $name }}</td>
                            <td class="num">{{ $count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="text-align:center;padding:12px;color:#94a3b8">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col">
            <div class="sec-head">By Level/Grade</div>
            <table class="dt">
                <thead><tr><th>Level</th><th class="num">Attendance</th></tr></thead>
                <tbody>
                    @forelse($byLevel as $name => $count)
                        <tr class="{{ $loop->index % 2 === 1 ? 'ev' : '' }}">
                            <td>{{ $name }}</td>
                            <td class="num">{{ $count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="text-align:center;padding:12px;color:#94a3b8">No data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="section">
    <div class="sec-head">Sessions ({{ count($sessionRows) }})</div>
    <table class="dt">
        <thead>
            <tr>
                <th>Date</th>
                <th>Training</th>
                <th>Level</th>
                <th class="num">Attendees</th>
                <th class="num">Satisfaction</th>
                <th class="num">Stars</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sessionRows as $i => $r)
                <tr class="{{ $i % 2 === 1 ? 'ev' : '' }}">
                    <td>{{ $r['date'] }}</td>
                    <td>{{ $r['training_name'] }}</td>
                    <td>{{ $r['level_name'] }}</td>
                    <td class="num">{{ $r['attendees'] }}</td>
                    <td class="num">{{ $r['avg_satisfaction'] ?? '–' }}</td>
                    <td class="num">{{ $r['avg_stars'] }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:12px;color:#94a3b8">No data available</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="rpt-footer">
    <div class="ft-l">Training Report</div>
    <div class="ft-r">Generated {{ now()->format('d M Y, H:i') }}</div>
</div>

</div>
</body>
</html>
