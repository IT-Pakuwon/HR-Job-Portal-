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
    font-size: 9px;
    color: #1e293b;
    background: #ffffff;
}

.rpt-header {
    background-color: #1e1b4b;
    padding: 16px 20px 14px;
}
.rpt-header h1 {
    font-size: 18px;
    font-weight: bold;
    color: #ffffff;
    margin-bottom: 4px;
}
.rpt-header .meta {
    font-size: 8px;
    color: #c7d2fe;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px solid #4338ca;
}
.rpt-header .meta span { margin-right: 20px; }

.rpt-header-stripe {
    height: 4px;
    background-color: #4f46e5;
}

.page-wrap {
    padding: 18px 22px 0;
}

.dt { width: 100%; border-collapse: collapse; }
.dt thead th {
    background-color: #1e1b4b;
    color: #e0e7ff;
    font-weight: bold;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 7px 10px;
    text-align: left;
}
.dt tbody td {
    padding: 6px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 8.5px;
    color: #1e293b;
}
.dt tbody .ev { background-color: #f8f7ff; }

.rpt-footer {
    display: table;
    width: 100%;
    border-top: 2px solid #4f46e5;
    padding-top: 7px;
    margin-top: 18px;
}
.rpt-footer .ft-l {
    display: table-cell;
    text-align: left;
    font-size: 7px;
    font-weight: bold;
    color: #4f46e5;
}
.rpt-footer .ft-r {
    display: table-cell;
    text-align: right;
    font-size: 7px;
    color: #94a3b8;
}
</style>
</head>
<body>

<div class="rpt-header">
    <h1>Training Attendance — {{ $trainingName }}</h1>
    <div class="meta">
        <span><strong>Event Date:</strong> {{ \Carbon\Carbon::parse($scheduleDate)->format('d M Y') }}</span>
        <span><strong>Attendees:</strong> {{ count($rows) }}</span>
        <span><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</span>
    </div>
</div>
<div class="rpt-header-stripe"></div>

<div class="page-wrap">
    <table class="dt">
        <thead>
            <tr>
                <th style="width:18%">Doc ID</th>
                <th style="width:26%">Name</th>
                <th style="width:20%">Company</th>
                <th style="width:18%">Department</th>
                <th style="width:18%">Attended At</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $r)
                <tr @if($i % 2 === 1) class="ev" @endif>
                    <td>{{ $r['docid'] }}</td>
                    <td>{{ $r['name'] }}</td>
                    <td>{{ $r['cpny_name'] ?? '-' }}</td>
                    <td>{{ $r['department_name'] ?? '-' }}</td>
                    <td>{{ $r['attended_at'] ? \Carbon\Carbon::parse($r['attended_at'])->format('d M Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:10px">No attendees</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="rpt-footer">
        <div class="ft-l">Training Attendance</div>
        <div class="ft-r">{{ $trainingName }} &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y, H:i') }}</div>
    </div>
</div>

</body>
</html>
