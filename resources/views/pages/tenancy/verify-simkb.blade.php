<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify SIMKB {{ $simkb->simkbnbr }}</title>
    <style>
        body { margin: 0; background: #f1f5f9; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; color: #1e293b; }
        .wrap { max-width: 440px; margin: 0 auto; padding: 32px 16px; }
        .card { background: #fff; border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(15, 23, 42, .1); }
        .eyebrow { font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; margin: 0; }
        .head { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px; }
        h1 { font-size: 20px; margin: 0; }
        .pill { font-size: 12px; font-weight: 700; color: #fff; padding: 4px 12px; border-radius: 999px; }
        dl { margin: 22px 0 0; font-size: 14px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px solid #f1f5f9; }
        .row:last-child { border-bottom: 0; }
        dt { color: #64748b; } dd { margin: 0; font-weight: 600; text-align: right; }
        .foot { margin: 20px 0 0; padding-top: 14px; border-top: 1px solid #f1f5f9; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
@php
    $colors = ['P' => '#d97706', 'H' => '#ea580c', 'R' => '#dc2626', 'C' => '#16a34a', 'E' => '#2563eb', 'X' => '#6b7280'];
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d M Y') : '-';
@endphp
    <div class="wrap">
        <div class="card">
            <p class="eyebrow">Surat Ijin Masuk Barang</p>
            <div class="head">
                <h1>{{ $simkb->simkbnbr }}</h1>
                <span class="pill" style="background: {{ $colors[$simkb->status] ?? '#6b7280' }};">{{ $statusLabel }}</span>
            </div>

            <dl>
                <div class="row"><dt>Site</dt><dd>{{ $simkb->sitename }}</dd></div>
                <div class="row"><dt>Tenant</dt><dd>{{ $simkb->storename }}</dd></div>
                <div class="row"><dt>Requester</dt><dd>{{ $simkb->requestname }}</dd></div>
                <div class="row"><dt>Entry Period</dt><dd>{{ $d($simkb->startdate) }} - {{ $d($simkb->enddate) }}</dd></div>
                @if ($simkb->totalextend)
                    <div class="row"><dt>Extended Period</dt><dd>{{ $d($simkb->extendstartdate) }} - {{ $d($simkb->extendenddate) }}</dd></div>
                @endif
                <div class="row"><dt>Work Hour</dt><dd>{{ $simkb->workhour }}</dd></div>
            </dl>

            <p class="foot">This page confirms the printed permit matches the system record, checked on {{ now()->format('d M Y H:i') }}.</p>
        </div>
    </div>
</body>
</html>
