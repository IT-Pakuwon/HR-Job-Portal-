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

/* ─── Report header ─────────────────────────────────────────────────── */
.rpt-header {
    background-color: #3b0764;
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
    color: #c4b5fd;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px solid #6d28d9;
}
.rpt-header .meta span { margin-right: 20px; }

/* Accent stripe below header */
.rpt-header-stripe {
    height: 4px;
    background-color: #7c3aed;
}

/* Content wrapper — provides left/right whitespace inside DomPDF page */
.page-wrap {
    padding: 16px 22px 0;
}

/* ─── Section ───────────────────────────────────────────────────────── */
.section { margin-top: 16px; }

.sec-head {
    font-size: 7.5px;
    font-weight: bold;
    color: #4c1d95;
    background-color: #f5f3ff;
    padding: 6px 12px 6px 14px;
    border-left: 4px solid #7c3aed;
    border-bottom: 1px solid #ddd6fe;
    margin-bottom: 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ─── Summary ───────────────────────────────────────────────────────── */
.sum-tbl { width: 100%; border-collapse: collapse; }
.sum-tbl td {
    width: 20%;
    padding: 13px 12px 11px;
    text-align: center;
    border: 1px solid #e9e0ff;
    vertical-align: top;
    background-color: #fafbff;
}
.s-lbl  {
    font-size: 6px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 7px;
    font-weight: bold;
}
.s-val  {
    font-size: 14px;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 4px;
    line-height: 1.1;
}
.s-note { font-size: 6px; color: #94a3b8; }
.c-gr  { color: #059669; }
.c-am  { color: #d97706; }
.c-re  { color: #dc2626; }

/* ─── Data tables ───────────────────────────────────────────────────── */
.dt { width: 100%; border-collapse: collapse; }

.dt thead th {
    background-color: #1e1b4b;
    color: #e0e7ff;
    font-weight: bold;
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 7px 10px;
    text-align: left;
}
.dt thead th.nr { text-align: right; }

.dt tbody td {
    padding: 5.5px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 8px;
    color: #1e293b;
}
.dt tbody .ev { background-color: #f8f7ff; }

.dt tfoot td {
    padding: 6px 10px;
    background-color: #1e1b4b;
    font-weight: bold;
    font-size: 8px;
    color: #e0e7ff;
}

.nr { text-align: right; }
.bl { font-weight: bold; }
.gr { color: #059669; }
.am { color: #d97706; }
.re { color: #dc2626; }

/* ─── Monthly table ─────────────────────────────────────────────────── */
.mt { width: 100%; border-collapse: collapse; }
.mt th {
    text-align: center;
    padding: 7px 3px;
    font-size: 7.5px;
    font-weight: bold;
    background-color: #1e1b4b;
    color: #e0e7ff;
}
.mt td {
    text-align: center;
    padding: 5px 3px;
    font-size: 7.5px;
    border-bottom: 1px solid #f1f5f9;
}
.mt .used-row   td { background-color: #f8f7ff; color: #475569; }
.mt .cumul-row  td { background-color: #ede9fe; color: #5b21b6; font-weight: bold; }

/* ─── Section divider ───────────────────────────────────────────────── */
.divider {
    height: 1px;
    background-color: #e9e0ff;
    margin: 16px 0 0;
}

/* ─── Footer ────────────────────────────────────────────────────────── */
.rpt-footer {
    display: table;
    width: 100%;
    border-top: 2px solid #7c3aed;
    padding-top: 7px;
    margin-top: 18px;
}
.rpt-footer .ft-l {
    display: table-cell;
    text-align: left;
    font-size: 6.5px;
    font-weight: bold;
    color: #7c3aed;
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
    $utilClass = function (float $pct): string {
        if ($pct >= 80) return 'c-re';
        if ($pct >= 60) return 'c-am';
        return 'c-gr';
    };
    $rowClass = function (float $pct): string {
        if ($pct >= 80) return 're';
        if ($pct >= 60) return 'am';
        return 'gr';
    };
    $deptList = collect($deptRows);
    $actList  = collect($actRows);
@endphp

{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="rpt-header">
    <h1>GM Report Dashboard</h1>
    <div class="meta">
        <span><strong>Period:</strong> {{ $dateFrom }} &ndash; {{ $dateTo }}</span>
        <span><strong>Company:</strong> {{ $cpnyId ?: 'All Companies' }}</span>
        <span><strong>Generated:</strong> {{ now()->format('d M Y, H:i') }}</span>
    </div>
</div>
<div class="rpt-header-stripe"></div>
<div class="page-wrap">

{{-- ── Summary ──────────────────────────────────────────────────────────── --}}
@php $s = $summary; $util = $s['utilization_pct']; @endphp
<div class="section">
    <div class="sec-head">Summary</div>
    <table class="sum-tbl">
        <tr>
            <td>
                <div class="s-lbl">Total Budget</div>
                <div class="s-val">{{ $fmt($s['total_budget']) }}</div>
                <div class="s-note">Original + Additional</div>
            </td>
            <td>
                <div class="s-lbl">Total Used</div>
                <div class="s-val {{ $utilClass($util) }}">{{ $fmt($s['total_used']) }}</div>
                <div class="s-note">Absorbed budget</div>
            </td>
            <td>
                <div class="s-lbl">Total Reserved</div>
                <div class="s-val c-am">{{ $fmt($s['total_reserve']) }}</div>
                <div class="s-note">Committed / in-progress</div>
            </td>
            <td>
                <div class="s-lbl">Total Remaining</div>
                <div class="s-val c-gr">{{ $fmt($s['total_remaining']) }}</div>
                <div class="s-note">Available balance</div>
            </td>
            <td>
                <div class="s-lbl">Utilization</div>
                <div class="s-val {{ $utilClass($util) }}">{{ number_format($util, 1) }}%</div>
                <div class="s-note">Used / Total Budget</div>
            </td>
        </tr>
    </table>
</div>

{{-- ── By Department ────────────────────────────────────────────────────── --}}
@php
    $dTot = $deptList->reduce(function ($c, $r) {
        return [
            'f' => $c['f'] + (float)($r->total_final     ?? 0),
            'u' => $c['u'] + (float)($r->total_used      ?? 0),
            'v' => $c['v'] + (float)($r->total_reserve   ?? 0),
            'r' => $c['r'] + (float)($r->total_remaining ?? 0),
        ];
    }, ['f' => 0, 'u' => 0, 'v' => 0, 'r' => 0]);
    $dTotPct = $dTot['f'] > 0 ? ($dTot['u'] / $dTot['f'] * 100) : 0;
@endphp
<div class="section">
    <div class="sec-head">By Department &mdash; {{ $deptList->count() }} dept(s)</div>
    <table class="dt">
        <thead>
            <tr>
                <th style="width:27%">Department</th>
                <th class="nr" style="width:17%">Budget</th>
                <th class="nr" style="width:15%">Used</th>
                <th class="nr" style="width:15%">Reserved</th>
                <th class="nr" style="width:15%">Remaining</th>
                <th class="nr" style="width:11%">Usage %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($deptRows as $r)
                @php $pct = (float)($r->used_pct ?? 0); @endphp
                <tr @if($loop->even) class="ev" @endif>
                    <td class="bl">{{ $r->department_fin_id ?? '—' }}</td>
                    <td class="nr">{{ $fmt((float)($r->total_final ?? 0)) }}</td>
                    <td class="nr {{ $rowClass($pct) }}">{{ $fmt((float)($r->total_used ?? 0)) }}</td>
                    <td class="nr am">{{ $fmt((float)($r->total_reserve ?? 0)) }}</td>
                    <td class="nr gr">{{ $fmt((float)($r->total_remaining ?? 0)) }}</td>
                    <td class="nr bl {{ $rowClass($pct) }}">{{ number_format($pct, 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:10px">No data</td></tr>
            @endforelse
        </tbody>
        @if ($deptList->isNotEmpty())
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="nr">{{ $fmt($dTot['f']) }}</td>
                <td class="nr">{{ $fmt($dTot['u']) }}</td>
                <td class="nr">{{ $fmt($dTot['v']) }}</td>
                <td class="nr">{{ $fmt($dTot['r']) }}</td>
                <td class="nr">{{ number_format($dTotPct, 1) }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

{{-- ── By Activity ──────────────────────────────────────────────────────── --}}
@php
    $aTot = $actList->reduce(function ($c, $r) {
        return [
            'f' => $c['f'] + (float)($r->total_final     ?? 0),
            'u' => $c['u'] + (float)($r->total_used      ?? 0),
            'v' => $c['v'] + (float)($r->total_reserve   ?? 0),
            'r' => $c['r'] + (float)($r->total_remaining ?? 0),
        ];
    }, ['f' => 0, 'u' => 0, 'v' => 0, 'r' => 0]);
    $aTotPct = $aTot['f'] > 0 ? ($aTot['u'] / $aTot['f'] * 100) : 0;
@endphp
<div class="section">
    <div class="sec-head">By Activity &mdash; {{ $actList->count() }} activit{{ $actList->count() === 1 ? 'y' : 'ies' }}</div>
    <table class="dt">
        <thead>
            <tr>
                <th style="width:32%">Activity</th>
                <th class="nr" style="width:15%">Budget</th>
                <th class="nr" style="width:14%">Used</th>
                <th class="nr" style="width:14%">Reserved</th>
                <th class="nr" style="width:14%">Remaining</th>
                <th class="nr" style="width:11%">Usage %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($actRows as $r)
                @php $pct = (float)($r->used_pct ?? 0); @endphp
                <tr @if($loop->even) class="ev" @endif>
                    <td class="bl">{{ $r->activity_descr ?? $r->activity_id ?? '—' }}</td>
                    <td class="nr">{{ $fmt((float)($r->total_final ?? 0)) }}</td>
                    <td class="nr {{ $rowClass($pct) }}">{{ $fmt((float)($r->total_used ?? 0)) }}</td>
                    <td class="nr am">{{ $fmt((float)($r->total_reserve ?? 0)) }}</td>
                    <td class="nr gr">{{ $fmt((float)($r->total_remaining ?? 0)) }}</td>
                    <td class="nr bl {{ $rowClass($pct) }}">{{ number_format($pct, 1) }}%</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:10px">No data</td></tr>
            @endforelse
        </tbody>
        @if ($actList->isNotEmpty())
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td class="nr">{{ $fmt($aTot['f']) }}</td>
                <td class="nr">{{ $fmt($aTot['u']) }}</td>
                <td class="nr">{{ $fmt($aTot['v']) }}</td>
                <td class="nr">{{ $fmt($aTot['r']) }}</td>
                <td class="nr">{{ number_format($aTotPct, 1) }}%</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

{{-- ── Monthly Trend ────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="sec-head">Monthly Absorption &mdash; {{ $year }}
        &nbsp;|&nbsp; Total Budget: {{ $fmt((float)$totalBudget) }}</div>
    <table class="mt">
        <thead>
            <tr>
                @foreach ($monthRows as $r)
                    <th>{{ $r['month'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            <tr class="used-row">
                @foreach ($monthRows as $r)
                    <td>{{ $fmt((float)$r['used']) }}</td>
                @endforeach
            </tr>
            <tr class="cumul-row">
                @foreach ($monthRows as $r)
                    <td>{{ $fmt((float)$r['cumulative']) }}</td>
                @endforeach
            </tr>
        </tbody>
    </table>
    <div style="font-size:7px;color:#94a3b8;margin-top:4px">
        Row 1: Monthly Used &nbsp;&bull;&nbsp; Row 2 (purple): Cumulative Used
    </div>
</div>

<div class="rpt-footer">
    <div class="ft-l">GM Report Dashboard</div>
    <div class="ft-r">
        {{ $dateFrom }} &ndash; {{ $dateTo }}
        &nbsp;&bull;&nbsp; {{ $cpnyId ?: 'All Companies' }}
        &nbsp;&bull;&nbsp; Generated {{ now()->format('d M Y, H:i') }}
    </div>
</div>

</div>{{-- end .page-wrap --}}
</body>
</html>
