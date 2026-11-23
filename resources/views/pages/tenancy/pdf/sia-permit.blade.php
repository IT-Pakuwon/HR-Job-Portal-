@php
    use Illuminate\Support\Carbon;

    $statusLabels = ['P' => 'Pending Approval', 'H' => 'On Hold / Revise', 'R' => 'Rejected', 'C' => 'Completed', 'E' => 'Extend Pending', 'X' => 'Cancelled'];
    $statusText = $statusLabels[$sia->status] ?? $sia->status;
    $statusColors = ['P' => '#d97706', 'H' => '#ea580c', 'R' => '#dc2626', 'C' => '#16a34a', 'E' => '#2563eb', 'X' => '#6b7280'];

    // "Senin, 14 November 2026" for the body, "14 Nov 26" for the signature blocks.
    $dayDate = fn ($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('l, d F Y') : '-';
    $longDate = fn ($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';
    $shortDate = fn ($d) => $d ? Carbon::parse($d)->format('d M y') : '';

    $name = fn ($u) => $users[trim((string) $u)]->name ?? $u;

    // $floorUnit ("Floor / Unit") is prepared by the controller.
    $sameDay = $sia->startdate && $sia->enddate && Carbon::parse($sia->startdate)->isSameDay(Carbon::parse($sia->enddate));
    $eventDate = $sameDay ? $dayDate($sia->startdate) : $dayDate($sia->startdate) . '  s/d  ' . $dayDate($sia->enddate);
    $eventTime = $sia->starttime && $sia->endtime ? substr($sia->starttime, 0, 5) . ' - ' . substr($sia->endtime, 0, 5) . ' WIB' : '-';

    // Per-site logo: public/images/sites/{SITEID}.png|svg. Falls back to the site name.
    $logoFile = collect(['png', 'svg'])
        ->map(fn ($ext) => public_path('images/sites/' . $sia->siteid . '.' . $ext))
        ->first(fn ($f) => is_file($f));
    $hasLogo = (bool) $logoFile;
    $logoW = $logoH = 0;
    $logoSrc = null;
    if ($hasLogo) {
        if (str_ends_with($logoFile, '.svg')) {
            $svg = file_get_contents($logoFile);
            preg_match('/viewBox="[\d.\-]+ [\d.\-]+ ([\d.]+) ([\d.]+)"/', $svg, $vb);
            $size = [(float) ($vb[1] ?? 100), (float) ($vb[2] ?? 100)];
            $logoSrc = 'data:image/svg+xml;base64,' . base64_encode($svg);
        } else {
            $size = @getimagesize($logoFile) ?: [100, 100];
            $logoSrc = $logoFile;
        }
        $scale = min(190 / $size[0], 56 / $size[1]);
        $logoW = round($size[0] * $scale);
        $logoH = round($size[1] * $scale);
    }

    // Signature table: the tenant asks, then Tenant Relation and the General Manager approve.
    // Normal approval steps are matched to those two columns in order; a column without a step stays blank for a wet signature.
    $approvalSteps = $trail->filter(fn ($s) => (float) $s->urutan > 0 && $s->conditiontype === 'Normal' && $s->aprvresult !== 'L')->values();
    $approverColumns = [
        'Tenant Relation' => $approvalSteps->get(0),
        'General Manager' => $approvalSteps->get(1),
    ];
    $stepActor = fn ($step) => ($step && $step->aprvresult === 'A') ? trim((string) ($step->lastupdate_user ?: $step->username)) : null;
    $submitStep = $trail->firstWhere('urutan', 0) ?? $trail->first();
    $submittedAt = $submitStep?->aprvdateafter ?? $sia->created_datetime;
    $mall = strtoupper($site?->sitename ?: 'mall');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Permohonan Ijin Acara {{ $sia->sianbr }}</title>
    <style>
        @page { margin: 38px 46px 40px 46px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #111827; line-height: 1.4; }
        table { border-collapse: collapse; }

        .head { width: 100%; }
        .head td { vertical-align: top; }
        .co-no { font-size: 12px; font-weight: bold; }
        .co-name { margin-top: 6px; font-size: 13px; font-weight: bold; }
        .co-line { font-size: 10px; margin-top: 2px; }
        .logo-text { font-size: 22px; font-weight: bold; color: #7f1d1d; text-align: right; }

        .title { margin: 14px 0 12px; text-align: center; font-size: 16px; font-weight: bold; letter-spacing: 0.3px; }
        .badge { display: inline-block; padding: 3px 9px; border-radius: 9px; font-size: 8.5px; font-weight: bold; color: #fff; }

        .f { width: 100%; }
        .f td { vertical-align: bottom; padding: 3px 0; }
        .f td.l { width: 31%; }
        .f td.c { width: 2%; }
        .f td.v { border-bottom: 1px solid #6b7280; font-weight: bold; }
        .f td.gap { width: 3%; }
        .f td.l2 { width: 15%; text-align: right; padding-right: 4px; }

        .grp td.num { width: 5%; vertical-align: top; padding-top: 3px; }
        .grp td.sub { width: 4%; }
        .grp td.l { width: 26%; }

        .intro { margin: 12px 0 8px; }
        .terms-title { margin: 12px 0 3px; }
        ol.terms { margin: 0; padding-left: 38px; text-align: justify; }
        ol.terms li { margin-bottom: 2px; }

        .sign { width: 100%; margin-top: 12px; border: 1px solid #111827; text-align: center; }
        .sign td { border: 1px solid #111827; vertical-align: top; }
        .sign .h { font-weight: bold; padding: 2px 4px; }
        .sign .box { height: 62px; padding: 3px; font-size: 8.5px; color: #374151; }
        .sign .small { text-align: left; padding: 1px 4px; font-size: 9.5px; }
        .note { margin: 3px 0 0 18px; font-size: 9.5px; font-style: italic; }
        .foot { margin-top: 16px; font-size: 9.5px; }
        .qr { width: 78px; height: 78px; }
        .checkbox { margin-top: 10px; padding: 5px 10px; border: 1px dashed #94a3b8; font-weight: bold; font-size: 10px; color: #1e3a5f; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width: 58%;">
                <div class="co-no">No. {{ $sia->siteid }}/{{ $sia->sianbr }}</div>
                <div class="co-name">{{ $site?->sitecompanyname }}</div>
                <div class="co-line">{{ $site?->siteaddress }}</div>
                <div class="co-line">Phone &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $site?->sitephone }}</div>
                <div class="co-line">Posko Security :</div>
            </td>
            <td style="width: 42%; text-align: right;">
                @if ($hasLogo)
                    <img src="{{ $logoSrc }}" style="width: {{ $logoW }}px; height: {{ $logoH }}px;" alt="{{ $site?->sitename }}">
                @else
                    <div class="logo-text">{{ $site?->sitename ?? $sia->siteid }}</div>
                @endif
                <div style="margin-top: 6px;"><span class="badge" style="background: {{ $statusColors[$sia->status] ?? '#6b7280' }};">{{ $statusText }}</span></div>
            </td>
        </tr>
    </table>

    <div class="title">PERMOHONAN IJIN ACARA</div>

    <table class="f">
        <tr>
            <td class="l">Nama Tenant</td><td class="c">:</td>
            <td class="v">{{ $sia->storename }}</td>
            <td class="gap"></td>
            <td class="l2" style="white-space: nowrap;">Lantai / No. Unit :</td>
            <td class="v" style="width: 22%;">{{ $floorUnit }}</td>
        </tr>
    </table>
    <table class="f">
        <tr><td class="l">Nama Penanggung Jawab</td><td class="c">:</td><td class="v">{{ $sia->requestpenanggungjawab }}</td></tr>
        <tr><td class="l">No. Telephone / Email</td><td class="c">:</td><td class="v">{{ $sia->requestphone }} &nbsp;/&nbsp; {{ $sia->requestemail }}</td></tr>
    </table>

    <p class="intro">Dengan ini memohon ijin untuk mengadakan acara / event, sebagai berikut :</p>

    <table class="f grp">
        <tr><td class="num" rowspan="5">1.</td><td class="sub">a.</td><td class="l">Hari / Tanggal</td><td class="c">:</td><td class="v">{{ $eventDate }}</td></tr>
        <tr><td class="sub">b.</td><td class="l">Waktu</td><td class="c">:</td><td class="v">{{ $eventTime }}</td></tr>
        <tr><td class="sub">c.</td><td class="l">Jenis Acara</td><td class="c">:</td><td class="v">{{ $sia->eventtype }}</td></tr>
        <tr><td class="sub">d.</td><td class="l">Lokasi</td><td class="c">:</td><td class="v">{{ $sia->eventlocation }}</td></tr>
        <tr><td class="sub">e.</td><td class="l">Jumlah tamu / media</td><td class="c">:</td><td class="v">{{ $sia->eventguests }}</td></tr>
    </table>
    @if ($sia->totalextend)
        <table class="f grp"><tr><td class="num"></td><td class="sub"></td><td class="l">Perpanjangan</td><td class="c">:</td><td class="v">{{ $longDate($sia->extendstartdate) }} &nbsp;s/d&nbsp; {{ $longDate($sia->extendenddate) }}</td></tr></table>
    @endif

    <table class="f grp" style="margin-top: 8px;">
        <tr><td class="num" rowspan="3">2.</td><td class="sub"></td><td class="l">Nama Koordinator Acara</td><td class="c">:</td><td class="v">{{ $sia->coordinatorname }}</td></tr>
        <tr><td class="sub"></td><td class="l">PIC Security</td><td class="c">:</td><td class="v">{{ $sia->picsecurity }}</td></tr>
        <tr><td class="sub"></td><td class="l">PIC Engineering</td><td class="c">:</td><td class="v">{{ $sia->picengineering }}</td></tr>
    </table>

    <table class="f grp" style="margin-top: 8px;">
        <tr><td class="num" rowspan="2">3.</td><td class="sub"></td><td class="l">Nama Event Organizer</td><td class="c">:</td><td class="v">{{ $sia->organizername }}</td></tr>
        <tr><td class="sub"></td><td class="l">Nama PIC Event Organizer</td><td class="c">:</td><td class="v">{{ $sia->organizerpic }}</td></tr>
    </table>

    <table class="f grp" style="margin-top: 8px;">
        <tr><td class="num">4.</td><td class="sub"></td><td class="l">Catatan</td><td class="c">:</td><td class="v">{!! nl2br(e($sia->catatan)) !!}</td></tr>
    </table>

    <p class="terms-title"><span style="display: inline-block; width: 27px;">5.</span>Tenant yang akan bertanda tangan dibawah ini menyetujui bahwa :</p>
    <ol type="a" class="terms">
        <li>Akan menjaga kebersihan area sekitar selama acara berlangsung.</li>
        <li>Akan bertanggung jawab dan mengganti segala kerusakan terhadap properti {{ $mall }} / pihak lainnya yang mungkin terjadi selama acara berlangsung.</li>
        <li>Tidak mengganggu ketertiban umum dan kenyamanan pengunjung {{ $mall }}.</li>
        <li>Jika ketiga poin di atas (a, b, c) tidak diindahkan oleh tenant, maka pihak Manajemen {{ $mall }} berhak menghentikan acara yang dilakukan dan meminta pertanggungjawaban sepenuhnya kepada tenant yang bersangkutan.</li>
        <li>Penggunaan area depan toko / koridor mall harus mengajukan desain layout terlebih dahulu (proposal) hingga mendapatkan persetujuan dari pihak Manajemen.</li>
        <li>Segala bentuk penempatan properti event di luar area sewa harus dialas karpet.</li>
    </ol>

    <table class="sign">
        <tr>
            <td class="h" style="width: 34%;">Pemohon,</td>
            <td class="h" colspan="2">Menyetujui,</td>
        </tr>
        <tr>
            <td class="h">Tenant</td>
            @foreach (array_keys($approverColumns) as $role)
                <td class="h" style="width: 33%;">{{ $role }}</td>
            @endforeach
        </tr>
        <tr>
            <td class="box">
                <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" class="qr" alt="QR">
            </td>
            @foreach ($approverColumns as $step)
                <td class="box">
                    @if ($stepActor($step))
                        <div style="margin-top: 22px; font-weight: bold; color: #16a34a;">Disetujui secara digital</div>
                    @endif
                </td>
            @endforeach
        </tr>
        <tr>
            <td class="small">Nama : {{ $name($sia->created_user) }}</td>
            @foreach ($approverColumns as $step)
                <td class="small">Nama : {{ $stepActor($step) ? $name($stepActor($step)) : '' }}</td>
            @endforeach
        </tr>
        <tr>
            <td class="small">Tgl &nbsp;&nbsp;&nbsp;&nbsp;: {{ $shortDate($submittedAt) }}</td>
            @foreach ($approverColumns as $step)
                <td class="small">Tgl &nbsp;&nbsp;&nbsp;&nbsp;: {{ $stepActor($step) ? $shortDate($step->aprvdateafter) : '' }}</td>
            @endforeach
        </tr>
    </table>
    <p class="note">Note : jika ada pemakaian area depan toko / koridor mall harus ada persetujuan dari TR Manager &amp; GM Mall.</p>

    <p class="foot"><strong><em>Lembar :</em></strong> &nbsp;&nbsp; <em><strong>Asli :</strong> Tenant Relation</em> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <em><strong>Scan Digital :</strong> Tenant</em></p>

    <div class="checkbox">Check In / Out Posko FCC :
        @foreach (($checkLog ?? []) as $cl)
            <span style="font-weight: normal; margin-left: 10px;">{{ $cl->action === 'IN' ? 'In' : 'Out' }} {{ Carbon::parse($cl->checked_at)->format('d/m/Y H:i') }} ({{ $cl->checked_by }})</span>
        @endforeach
    </div>
</body>
</html>
