<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<style>
@page { margin: 14mm 16mm; }
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5px; color: #1e293b; }

.title-bar { border: 2px solid #64748b; padding: 10px 14px; margin-bottom: 10px; }
.title-bar h1 { font-size: 18px; font-weight: bold; }

table.rpt { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
table.rpt td, table.rpt th { border: 1px solid #cbd5e1; padding: 5px 8px; vertical-align: top; }
table.rpt th { background-color: #ede9fe; color: #3730a3; font-weight: bold; text-align: center; }

.label-cell { background-color: #f8fafc; font-weight: bold; color: #64748b; width: 110px; }
.num { text-align: right; }
.center { text-align: center; }
.pct-cell { background-color: #f5f3ff; color: #5b21b6; font-weight: bold; text-align: center; }
.muted { color: #94a3b8; font-style: italic; font-size: 8.5px; }
.section-title { text-transform: uppercase; }
.footer-row td { border-top: 2px solid #64748b; background-color: #f8fafc; font-weight: bold; }

.result-box { border: 2px solid #64748b; text-align: center; padding: 10px; margin-bottom: 10px; }
.result-box .lbl { font-size: 10px; font-weight: bold; }
.result-box .big { font-size: 26px; font-weight: bold; margin-top: 4px; }
.result-box .txt { font-size: 10px; font-weight: bold; margin-top: 6px; }
</style>
</head>
<body>

<div class="title-bar">
    <h1>Security Tracking Performance Report</h1>
</div>

<table style="width:100%">
<tr>
<td style="width:130px;vertical-align:top;padding-right:10px;">
    <div class="result-box">
        <div class="lbl">RESULT</div>
        <div class="big">{{ $scoring['result'] }}%</div>
    </div>
    <div class="result-box">
        <div class="lbl">PERFORMANCE</div>
        <div class="txt">{{ $scoring['performance'] }}</div>
    </div>

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
    <table class="rpt" style="font-size:8px;">
        <tr><th colspan="2" style="text-align:left;">Performance Rating Scale</th></tr>
        @foreach ($tiers as $tier)
            <tr @if($tier['label'] === $scoring['performance']) style="background-color:#f5f3ff;" @endif>
                <td>{{ $tier['label'] }}</td>
                <td class="num" style="white-space:nowrap;width:55px;">{{ $tier['range'] }}</td>
            </tr>
        @endforeach
    </table>
</td>
<td style="vertical-align:top;">

<table class="rpt">
    <tr><td class="label-cell">Period</td><td>{{ $start }} &ndash; {{ $end }}</td></tr>
    <tr><td class="label-cell">Vendor</td><td>{{ $vendor }}</td></tr>
    <tr><td class="label-cell">Location</td><td>{{ $siteName }}</td></tr>
</table>

<table class="rpt">
    <tr>
        <th colspan="2">Personel</th>
        <th>Total Beacon User</th>
    </tr>
    <tr>
        <td>Duty Officer (Active Track User)</td>
        <td class="center">{{ $personnel['active_track_user'] ?? '—' }}</td>
        <td rowspan="2" class="center" style="font-size:16px;font-weight:bold;">{{ $personnel['total_beacon_user'] ?? '—' }}</td>
    </tr>
    <tr>
        <td>Substitute (Non Track User)</td>
        <td class="center">{{ $personnel['non_track_user'] ?? '—' }}</td>
    </tr>
</table>

<table class="rpt">
    <tr>
        <th colspan="2" class="section-title">Absent Discipline</th>
        <th style="width:70px">Target</th>
        <th style="width:70px">Actual</th>
        <th style="width:50px">%</th>
    </tr>
    <tr>
        <td class="center" style="width:20px">1</td>
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

<table class="rpt">
    <tr>
        <th colspan="3" class="section-title">Beacon Tracking Performance</th>
        <th style="width:70px">Target</th>
        <th style="width:70px">Actual</th>
        <th style="width:50px">%</th>
    </tr>
    @forelse ($pointCompletion['rows'] as $i => $row)
        <tr>
            @if ($i === 0)
                <td rowspan="{{ count($pointCompletion['rows']) }}" class="center" style="width:20px">1</td>
                <td rowspan="{{ count($pointCompletion['rows']) }}" style="width:150px;font-weight:bold;">Track Point Activities Completion</td>
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

<table class="rpt">
    <tr>
        <th colspan="3" class="section-title">Alert Point</th>
        <th style="width:50px">Qty</th>
        <th style="width:60px">Total</th>
    </tr>
    @forelse ($alertRows as $row)
        <tr>
            @if ($row['isFirst'])
                <td rowspan="{{ $row['groupSpan'] }}" class="center" style="width:20px">{{ $row['groupIndex'] }}</td>
                <td rowspan="{{ $row['groupSpan'] }}" style="width:130px;font-weight:bold;text-transform:uppercase;">{{ $row['aspect'] }}</td>
            @endif
            <td>{{ $row['reason'] }}</td>
            <td class="num">{{ $row['qty'] }}</td>
            @if ($row['isFirst'])
                <td rowspan="{{ $row['groupSpan'] }}" class="pct-cell">{{ $row['groupQty'] }}</td>
            @endif
        </tr>
    @empty
        <tr><td colspan="5" class="center muted">No data available</td></tr>
    @endforelse
    <tr class="footer-row">
        <td colspan="4" style="text-align:right;text-transform:uppercase;">Alert Raised</td>
        <td class="center">{{ $alertPoint['total_raised'] ?? 0 }}</td>
    </tr>
</table>

<table class="rpt">
    <tr>
        <th style="text-align:left;">Scoring Breakdown</th>
        <th style="width:60px">%</th>
        <th style="width:60px">Bobot</th>
        <th style="width:80px">Contribution</th>
    </tr>
    @foreach ($scoring['sections'] as $s)
        <tr>
            <td>{{ $s['label'] }}</td>
            <td class="center">{{ $s['pct'] }}%</td>
            <td class="center">{{ $s['bobot'] }}%</td>
            <td class="center">{{ $s['contribution'] }}%</td>
        </tr>
    @endforeach
    <tr class="footer-row">
        <td colspan="3" style="text-align:right;text-transform:uppercase;">Result</td>
        <td class="center">{{ $scoring['result'] }}%</td>
    </tr>
</table>

<p class="muted">Generated {{ now()->format('d M Y H:i') }}</p>

</td>
</tr>
</table>

</body>
</html>
