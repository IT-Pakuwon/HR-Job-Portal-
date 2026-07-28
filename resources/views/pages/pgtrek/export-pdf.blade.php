<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
@page {
    margin-top: 50px;
    margin-bottom: 80px;
    margin-left: 16mm;
    margin-right: 16mm;
}
* { margin: 0; padding: 0; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 9px;
    color: #1e293b;
    line-height: 1.45;
}

/* ----- Footer (safe bottom-margin anchored) ----- */
#footer {
    margin-top: 20px;
    padding: 6px 16px 0;
    border-top: 1px solid #e2e8f0;
    text-align: center;
    font-size: 7px;
    color: #94a3b8;
}
#footer .page-num:after {
    content: "Page " counter(page) " of " counter(pages);
}
#footer .disclaimer {
    font-style: italic;
    margin-top: 2px;
    color: #a0aec0;
}

/* ----- Header Banner ----- */
.header-banner {
    background-color: #1a365d;
    color: #ffffff;
    padding: 12px 16px;
    margin-bottom: 14px;
    width: 100%;
}
.header-banner h1 {
    font-size: 18px;
    font-weight: bold;
    margin: 0 0 2px;
}
.header-banner .timestamp {
    font-size: 7.5px;
    color: #a0aec0;
}

/* ----- KPI Summary Cards ----- */
table.kpi { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
table.kpi td {
    border: 1px solid #e2e8f0;
    padding: 10px 8px;
    text-align: center;
    background-color: #ffffff;
    vertical-align: top;
}
.kpi-label {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #718096;
    margin-bottom: 4px;
}
.kpi-value {
    font-size: 22px;
    font-weight: bold;
    color: #1a365d;
    margin-bottom: 2px;
}
.kpi-sub {
    font-size: 7.5px;
    color: #4a5568;
}

/* ----- Data Tables ----- */
table.rpt { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
table.rpt th {
    background-color: #2d3748;
    color: #ffffff;
    font-weight: bold;
    padding: 6px 8px;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 1px solid #2d3748;
}
table.rpt td {
    border: 1px solid #e2e8f0;
    padding: 5px 8px;
    vertical-align: top;
}
table.rpt tr:nth-child(even) td {
    background-color: #f7fafc;
}
table.rpt tr.footer-row td {
    background-color: #edf2f7;
    font-weight: bold;
    border-top: 2px solid #2d3748;
}
table.rpt tr.result-row td {
    background-color: #ebf4ff;
    font-weight: bold;
    border-top: 2px solid #2d3748;
}

/* ----- Performance Scale ----- */
table.scale { width: 100%; border-collapse: collapse; font-size: 7.5px; }
table.scale th {
    background-color: #4a5568;
    color: #fff;
    font-weight: bold;
    font-size: 8px;
    text-transform: uppercase;
    padding: 5px 8px;
    text-align: left;
    border: 1px solid #4a5568;
}
table.scale td {
    border: 1px solid #e2e8f0;
    padding: 4px 8px;
}
table.scale tr.active td {
    background-color: #ebf8ff;
    font-weight: bold;
    color: #2b6cb0;
}
table.scale tr:nth-child(even) td {
    background-color: #fafafa;
}

/* ----- Utility Classes ----- */
.label-cell { background-color: #f7fafc; font-weight: bold; color: #4a5568; width: 100px; }
.num { text-align: right; }
.center { text-align: center; }
.pct-cell { background-color: #ebf8ff; color: #2b6cb0; font-weight: bold; text-align: center; }
.muted { color: #a0aec0; font-style: italic; font-size: 7.5px; }
.section-label { font-weight: bold; }
.section-title { text-transform: uppercase; }
</style>
</head>
<body>

@php
    $tiers = [
        ['label' => 'Fully Compliant with Standard', 'range' => '>90%'],
        ['label' => 'Substantially Compliant with Standard', 'range' => '80% - 90%'],
        ['label' => 'Moderately Compliant with Standard', 'range' => '70% - 80%'],
        ['label' => 'Minimum Standard Performance', 'range' => '40% - 70%'],
        ['label' => 'Below Standard Performance', 'range' => '25% - 40%'],
        ['label' => 'Unacceptable Performance', 'range' => '<25%'],
    ];
@endphp

<!-- ===== EXECUTIVE HEADER BANNER ===== -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:14px;">
<tr>
    <td class="header-banner">
        <table width="100%">
        <tr>
            <td style="vertical-align:middle;">
                <h1>PgTrek Dashboard Executive Report</h1>
                <div class="timestamp">Security Tracking Performance &mdash; Executive Summary</div>
            </td>
            <td style="text-align:right;vertical-align:middle;width:180px;white-space:nowrap;">
                <div class="timestamp">
                    Generated {{ now()->format('d F Y H:i') }}<br>
                    {{ now()->format('T') }} Timezone
                </div>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- ===== REPORT METADATA ===== -->
<table class="rpt" style="margin-bottom:12px;">
<tr>
    <td class="label-cell">Reporting Period</td>
    <td style="width:140px;">{{ $start }} &ndash; {{ $end }}</td>
    <td class="label-cell" style="width:70px;">Vendor</td>
    <td style="width:130px;">{{ $vendor }}</td>
    <td class="label-cell" style="width:70px;">Location</td>
    <td>{{ $siteName }}</td>
</tr>
</table>

<!-- ===== KPI SUMMARY CARDS ===== -->
<table class="kpi">
<tr>
    <td style="width:16.66%;">
        <div class="kpi-label">Overall Result</div>
        <div class="kpi-value">{{ $scoring['result'] }}%</div>
        <div class="kpi-sub">{{ $scoring['performance'] }}</div>
    </td>
    <td style="width:16.66%;">
        <div class="kpi-label">Total Personnel</div>
        <div class="kpi-value">{{ $personnel['total_beacon_user'] ?? '—' }}</div>
        <div class="kpi-sub">Beacon Users</div>
    </td>
    <td style="width:16.66%;">
        <div class="kpi-label">Duty Officers</div>
        <div class="kpi-value">{{ $personnel['active_track_user'] ?? '—' }}</div>
        <div class="kpi-sub">Active Track Users</div>
    </td>
    <td style="width:16.66%;">
        <div class="kpi-label">Substitutes</div>
        <div class="kpi-value">{{ $personnel['non_track_user'] ?? '—' }}</div>
        <div class="kpi-sub">Non Track Users</div>
    </td>
    <td style="width:16.66%;">
        <div class="kpi-label">Attendance Rate</div>
        <div class="kpi-value">{{ $absentDiscipline['work_days_pct'] !== null ? $absentDiscipline['work_days_pct'].'%' : '—' }}</div>
        <div class="kpi-sub">Work Days Compliance</div>
    </td>
    <td style="width:16.66%;">
        <div class="kpi-label">Alerts Raised</div>
        <div class="kpi-value">{{ $alertPoint['total_raised'] ?? 0 }}</div>
        <div class="kpi-sub">Total Incidents</div>
    </td>
</tr>
</table>

<!-- ===== PERSONNEL SUMMARY ===== -->
<table class="rpt">
<tr>
    <th colspan="2" style="text-align:left;">Personnel Summary</th>
    <th style="width:70px;">Count</th>
</tr>
<tr>
    <td class="center" style="width:20px;">1</td>
    <td>Duty Officer (Active Track User)</td>
    <td class="num">{{ $personnel['active_track_user'] ?? '—' }}</td>
</tr>
<tr>
    <td class="center">2</td>
    <td>Substitute (Non Track User)</td>
    <td class="num">{{ $personnel['non_track_user'] ?? '—' }}</td>
</tr>
</table>

<!-- ===== ABSENT DISCIPLINE ===== -->
<table class="rpt">
<tr>
    <th colspan="2" style="text-align:left;">Absent Discipline</th>
    <th style="width:70px;">Target</th>
    <th style="width:70px;">Actual</th>
    <th style="width:50px;">%</th>
</tr>
<tr>
    <td class="center" style="width:20px;">1</td>
    <td>Work Days</td>
    <td class="num">{{ $absentDiscipline['work_days_target'] ?? '—' }}</td>
    <td class="num">{{ $absentDiscipline['work_days_actual'] ?? '—' }}</td>
    <td rowspan="2" class="pct-cell">{{ $absentDiscipline['work_days_pct'] !== null ? $absentDiscipline['work_days_pct'].'%' : '—' }}</td>
</tr>
<tr>
    <td class="center">2</td>
    <td>Substitute Used <span class="muted">(no reliable source found)</span></td>
    <td class="num muted">&mdash;</td>
    <td class="num muted">&mdash;</td>
</tr>
</table>

<!-- ===== BEACON TRACKING PERFORMANCE ===== -->
<table class="rpt">
<tr>
    <th colspan="3" style="text-align:left;">Beacon Tracking Performance</th>
    <th style="width:65px;">Target</th>
    <th style="width:65px;">Actual</th>
    <th style="width:50px;">%</th>
</tr>
@forelse ($pointCompletion['rows'] as $i => $row)
    <tr>
        @if ($i === 0)
            <td rowspan="{{ count($pointCompletion['rows']) }}" class="center" style="width:20px;">1</td>
            <td rowspan="{{ count($pointCompletion['rows']) }}" style="width:140px;font-weight:bold;">Track Point Activities Completion</td>
        @endif
        <td>Track - {{ $row['activity'] }}</td>
        <td class="num">{{ $row['target'] }}</td>
        <td class="num">{{ $row['actual'] }}</td>
        @if ($i === 0)
            <td rowspan="{{ count($pointCompletion['rows']) }}" class="pct-cell">{{ $pointCompletion['overall_pct'] !== null ? $pointCompletion['overall_pct'].'%' : '—' }}</td>
        @endif
    </tr>
@empty
    <tr><td colspan="6" class="center muted">No data available</td></tr>
@endforelse
@forelse ($timeImplement['rows'] as $i => $row)
    <tr>
        @if ($i === 0)
            <td rowspan="{{ count($timeImplement['rows']) }}" class="center">2</td>
            <td rowspan="{{ count($timeImplement['rows']) }}" style="font-weight:bold;">Track Time Implement (Minutes)</td>
        @endif
        <td>Time - {{ $row['activity'] }}</td>
        <td class="num">{{ number_format($row['target_minutes']) }} min</td>
        <td class="num">{{ number_format($row['actual_minutes']) }} min</td>
        @if ($i === 0)
            <td rowspan="{{ count($timeImplement['rows']) }}" class="pct-cell">{{ $timeImplement['overall_pct'] !== null ? $timeImplement['overall_pct'].'%' : '—' }}</td>
        @endif
    </tr>
@empty
    <tr><td colspan="6" class="center muted">No data available</td></tr>
@endforelse
</table>

<!-- ===== ALERT POINT ===== -->
<table class="rpt">
<tr>
    <th colspan="2" style="text-align:left;">Alert Point</th>
    <th style="text-align:left;">Reason</th>
    <th style="width:50px;">Qty</th>
</tr>
@forelse ($alertRows as $row)
    <tr>
        @if ($row['isFirst'])
            <td rowspan="{{ $row['groupSpan'] }}" class="center" style="width:20px;">{{ $row['groupIndex'] }}</td>
            <td rowspan="{{ $row['groupSpan'] }}" style="width:130px;font-weight:bold;text-transform:uppercase;">{{ $row['aspect'] }}</td>
        @endif
        <td>{{ $row['reason'] }}</td>
        <td class="num">{{ $row['qty'] }}</td>
    </tr>
@empty
    <tr><td colspan="4" class="center muted">No data available</td></tr>
@endforelse
<tr class="footer-row" style="background-color:#edf2f7;font-weight:bold;border-top:2px solid #2d3748;">
    <td colspan="3" style="text-align:right;text-transform:uppercase;letter-spacing:0.5px;">TOTAL:</td>
    <td class="center" style="font-size:11px;font-weight:bold;color:#1a365d;">{{ $alertPoint['total_raised'] ?? 0 }}</td>
</tr>
</table>

<!-- ===== SCORING BREAKDOWN ===== -->
<table class="rpt">
<tr>
    <th style="text-align:left;">Scoring Breakdown</th>
    <th style="width:60px;">%</th>
    <th style="width:60px;">Bobot</th>
    <th style="width:80px;">Contribution</th>
</tr>
@foreach ($scoring['sections'] as $s)
    <tr>
        <td>{{ $s['label'] }}</td>
        <td class="center">{{ $s['pct'] }}%</td>
        <td class="center">{{ $s['bobot'] }}%</td>
        <td class="center">{{ $s['contribution'] }}%</td>
    </tr>
@endforeach
<tr class="result-row">
    <td colspan="3" style="text-align:right;text-transform:uppercase;letter-spacing:1px;">Final Result</td>
    <td class="center" style="font-size:12px;font-weight:bold;color:#1a365d;">{{ $scoring['result'] }}%</td>
</tr>
</table>

<!-- ===== PERFORMANCE RATING SCALE ===== -->
<table class="scale">
<tr><th colspan="2">Performance Rating Scale</th></tr>
@foreach ($tiers as $tier)
    <tr @if($tier['label'] === $scoring['performance']) class="active" @endif>
        <td>{{ $tier['label'] }}</td>
        <td class="num" style="width:65px;">{{ $tier['range'] }}</td>
    </tr>
@endforeach
</table>

<!-- ===== FIXED FOOTER ===== -->
<div id="footer">
    <div class="page-num"></div>
    <div class="disclaimer">This report is system-generated based on available tracking data. The information presented reflects the most current data recorded and is provided for management assessment purposes only.</div>
</div>

</body>
</html>
