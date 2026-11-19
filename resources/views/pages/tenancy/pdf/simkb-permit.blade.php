@php
    use Illuminate\Support\Carbon;

    $statusLabels = ['P' => 'Pending Approval', 'H' => 'On Hold / Revise', 'R' => 'Rejected', 'C' => 'Completed', 'E' => 'Extend Pending', 'X' => 'Cancelled'];
    $statusText = $statusLabels[$simkb->status] ?? $simkb->status;
    $statusColors = ['P' => '#d97706', 'H' => '#ea580c', 'R' => '#dc2626', 'C' => '#16a34a', 'E' => '#2563eb', 'X' => '#6b7280'];

    // Masuk (in) / Keluar (out) / both: drives the document title and the wording of the labels.
    $t = strtolower((string) $simkb->simkbtypeid);
    $mode = str_contains($t, 'masuk') && str_contains($t, 'keluar') ? 'both' : (str_contains($t, 'keluar') ? 'keluar' : 'masuk');
    $docTitle = ['masuk' => 'MASUK BARANG', 'keluar' => 'KELUAR BARANG', 'both' => 'MASUK / KELUAR BARANG'][$mode];
    $verb = ['masuk' => 'Masuk', 'keluar' => 'Keluar', 'both' => 'Masuk / Keluar'][$mode];
    $introNoun = ['masuk' => 'Pemasukan', 'keluar' => 'Pengeluaran', 'both' => 'Pemasukan / Pengeluaran'][$mode];
    $longDate = fn ($d) => $d ? Carbon::parse($d)->locale('id')->translatedFormat('d F Y') : '-';
    $shortDate = fn ($d) => $d ? Carbon::parse($d)->format('d M y') : '';
    $time = fn ($d) => $d ? Carbon::parse($d)->format('H:i') : '';

    $name = fn ($u) => $users[trim((string) $u)]->name ?? $u;
    $role = fn ($u) => ($dept = ($users[trim((string) $u)]->departmentid ?? null)) ? ($departmentNames[$dept] ?? '') : '';

    // $floorUnit ("Store / Floor / Unit") is prepared by the controller.
    $fromDept = $departmentNames[$simkb->departmentid] ?? $simkb->departmentid;

    // Per-site logo: public/images/sites/{SITEID}.png or .svg
    $logoFile = collect(['png', 'svg'])
        ->map(fn ($ext) => public_path('images/sites/' . $simkb->siteid . '.' . $ext))
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
        $scale = min(140 / $size[0], 48 / $size[1]);
        $logoW = round($size[0] * $scale);
        $logoH = round($size[1] * $scale);
    }

    $submitStep = $trail->firstWhere('urutan', 0) ?? $trail->first();
    $submittedAt = $submitStep?->aprvdateafter ?? $simkb->created_datetime;
    $approvalSteps = $trail->filter(fn ($s) => (float) $s->urutan > 0 && $s->aprvresult !== 'L')->values();
    $stepLabels = ['A' => 'Disetujui', 'P' => 'Menunggu', 'R' => 'Ditolak', 'D' => 'Revisi', 'E' => 'Perpanjangan'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Ijin {{ $docTitle }} {{ $simkb->simkbnbr }}</title>
    <style>
        @page { margin: 100px 46px 70px 46px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10.5px; color: #1f2937; line-height: 1.45; }

        .page-header { position: fixed; top: -86px; left: 0; right: 0; height: 66px; border-bottom: 2px solid #1e3a5f; }
        .page-header table { width: 100%; border-collapse: collapse; }
        .page-header td { vertical-align: middle; }
        .logo { height: 48px; }
        .logo-text { font-size: 22px; font-weight: bold; color: #1e3a5f; letter-spacing: 0.5px; }
        .doc-title { text-align: center; font-size: 16px; font-weight: bold; color: #1e3a5f; letter-spacing: 2px; }
        .doc-sub { text-align: center; font-size: 8px; color: #6b7280; letter-spacing: 1px; text-transform: uppercase; }
        .doc-status { text-align: right; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; color: #fff; letter-spacing: 0.3px; }

        .page-footer { position: fixed; bottom: -60px; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 5px; text-align: center; font-size: 7.5px; line-height: 1.4; color: #6b7280; }
        .page-footer strong { color: #374151; }

        table { border-collapse: collapse; }
        .intro { margin: 0 0 8px; font-size: 11px; color: #374151; }

        .card { width: 100%; border: 1px solid #e2e8f0; }
        .fields { width: 100%; }
        .fields td { vertical-align: top; padding: 3px 10px; border-bottom: 1px solid #eef2f7; }
        .fields tr:last-child td { border-bottom: none; }
        .fields td.l { width: 24%; white-space: nowrap; background: #f4f7fb; color: #1e3a5f; font-weight: bold; font-size: 9px; text-transform: uppercase; letter-spacing: 0.4px; border-right: 1px solid #e2e8f0; }
        .fields td.v { font-size: 10.5px; }

        .fields td.qr-cell { width: 120px; text-align: center; vertical-align: middle; border-left: 1px solid #e2e8f0; border-bottom: none; background: #fff; }
        .qr-cell .code { font-weight: bold; font-size: 8.5px; margin-top: 3px; color: #1e3a5f; letter-spacing: 0.3px; }

        .section-title { margin: 12px 0 5px; padding-left: 7px; border-left: 3px solid #1e3a5f; font-size: 11px; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.6px; }
        .notes td { vertical-align: top; padding: 1px 0 2px; text-align: justify; font-size: 8.5px; line-height: 1.35; color: #374151; }
        .notes td.n { width: 22px; font-weight: bold; color: #1e3a5f; }

        .closing { margin: 8px 0 0; font-size: 10.5px; }
        .sign { width: 100%; text-align: center; }
        .sign td { vertical-align: top; }
        .sign-head { font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #1e3a5f; padding-bottom: 4px; border-bottom: 1px solid #cbd5e1; }
        .signer { font-size: 9.5px; line-height: 1.4; padding: 0 6px; }
        .signer .who { font-weight: bold; }
        .signer .state { font-size: 8.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.4px; color: #1e3a5f; }
        .signer .gap { height: 16px; }
        .signer .line { border-top: 1px solid #9ca3af; margin: 0 8px 3px; }
        .muted { color: #6b7280; font-size: 8.5px; }
        .checkbox { margin-top: 8px; padding: 5px 10px; border: 1px dashed #94a3b8; font-weight: bold; font-size: 10px; color: #1e3a5f; }
    </style>
</head>
<body>
    <div class="page-header">
        <table>
            <tr>
                <td style="width: 28%;">
                    @if ($hasLogo)
                        <img src="{{ $logoSrc }}" style="width: {{ $logoW }}px; height: {{ $logoH }}px;" alt="{{ $site?->sitename }}">
                    @else
                        <span class="logo-text">{{ $site?->sitename ?? $simkb->siteid }}</span>
                    @endif
                </td>
                <td style="width: 44%;">
                    <div class="doc-title">SURAT IJIN</div>
                    <div class="doc-title" style="font-size: 12px; letter-spacing: 1.5px;">{{ $docTitle }}</div>
                    <div class="doc-sub">No. {{ $simkb->siteid }}/{{ $simkb->simkbnbr }}</div>
                </td>
                <td class="doc-status" style="width: 28%;">
                    <span class="badge" style="background: {{ $statusColors[$simkb->status] ?? '#6b7280' }};">{{ $statusText }}</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        <strong>{{ $site?->sitecompanyname }}</strong><br>
        {{ $site?->siteaddress }}<br>
        @if ($site?->sitephone)<strong>Phone : {{ $site->sitephone }}</strong>@endif
        @if ($site?->sitefax)<strong> / Fax : {{ $site->sitefax }}</strong>@endif
    </div>

    <p class="intro">Agar dibantu pengawasan {{ $introNoun }} Barang sebagai berikut :</p>

    <table class="card">
        <tr><td>
            <table class="fields">
                <tr>
                    <td class="l">Kepada</td>
                    <td class="v">Dept Security</td>
                    <td class="qr-cell" rowspan="5">
                        <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" style="width: 96px; height: 96px;" alt="QR">
                        <div class="code">{{ $simkb->siteid }}/{{ $simkb->simkbnbr }}</div>
                    </td>
                </tr>
                <tr>
                    <td class="l">Dari</td>
                    <td class="v">{{ $fromDept }}</td>
                </tr>
                <tr>
                    <td class="l">Toko / Lantai / Unit</td>
                    <td class="v">{{ $floorUnit ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="l">Penanggung Jawab</td>
                    <td class="v">{{ $simkb->requestpenanggungjawab ?: $simkb->requestname }}</td>
                </tr>
                <tr>
                    <td class="l">Phone / Email</td>
                    <td class="v">{{ $simkb->requestphone }} / {{ $simkb->requestemail }}</td>
                </tr>
            </table>
        </td></tr>
    </table>

    <div class="section-title">Detail Barang</div>
    <table class="card">
        <tr><td>
            <table class="fields">
                <tr>
                    <td class="l">Tanggal {{ $verb }}</td>
                    <td class="v"><strong>{{ $longDate($simkb->startdate) }}</strong> s/d <strong>{{ $longDate($simkb->enddate) }}</strong></td>
                </tr>
                @if ($simkb->totalextend)
                    <tr>
                        <td class="l">Periode Perpanjangan</td>
                        <td class="v"><strong>{{ $longDate($simkb->extendstartdate) }}</strong> s/d <strong>{{ $longDate($simkb->extendenddate) }}</strong></td>
                    </tr>
                @endif
                @if ($simkb->isevent)
                    <tr>
                        <td class="l">Event</td>
                        <td class="v">{{ $simkb->namaevent }} ({{ $longDate($simkb->periodeawalevent) }} s/d {{ $longDate($simkb->periodeakhirevent) }}) @ {{ $simkb->lokasievent }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="l">Pintu {{ $verb }}</td>
                    <td class="v">{{ $gateName }}</td>
                </tr>
                <tr>
                    <td class="l">Waktu Kerja</td>
                    <td class="v">
                        {{ $simkb->workhour }}
                        @if ($workHourNote)
                            <div style="margin-top: 2px; font-size: 9.5px; color: #4b5563;">{!! nl2br(e(trim($workHourNote))) !!}</div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="l">Detail Barang {{ $mode === 'keluar' ? 'Keluar' : 'Masuk' }}</td>
                    <td class="v">{!! nl2br(e($simkb->workdescription)) !!}</td>
                </tr>
                @if ($mode === 'both')
                    <tr>
                        <td class="l">Detail Barang Keluar</td>
                        <td class="v">{!! nl2br(e($simkb->workdescription_keluar ?: '-')) !!}</td>
                    </tr>
                @endif
                @if ($simkb->workerdescription)
                    <tr>
                        <td class="l">Pekerja</td>
                        <td class="v">{!! nl2br(e($simkb->workerdescription)) !!}</td>
                    </tr>
                @endif
            </table>
        </td></tr>
    </table>

    @if ($notes->isNotEmpty() || $simkb->catatan)
        <div class="section-title">Catatan</div>
        <table class="notes" style="width: 100%;">
            @foreach ($notes as $note)
                <tr>
                    <td class="n">{{ $loop->iteration }}.</td>
                    <td>{{ $note->note }}</td>
                </tr>
            @endforeach
            @if ($simkb->catatan)
                <tr>
                    <td class="n">{{ $notes->count() + 1 }}.</td>
                    <td>{!! nl2br(e($simkb->catatan)) !!}</td>
                </tr>
            @endif
        </table>
    @endif

    <div style="page-break-inside: avoid;">
        <p class="closing">
            Demikian surat ijin ini kami berikan untuk dipergunakan sebagaimana mestinya.<br>
            <strong>Jakarta, {{ $longDate($simkb->simkbdate) }}</strong>
        </p>

        <table class="sign" style="margin-top: 8px;">
            <tr>
                <td style="width: 34%;"><div class="sign-head" style="margin-right: 8px;">Dibuat Oleh</div></td>
                <td><div class="sign-head">Disetujui Oleh</div></td>
            </tr>
            <tr>
                <td>
                    <table class="sign" style="margin-top: 8px;">
                        <tr>
                            <td class="signer">
                                <div class="state">Mengajukan</div>
                                <div class="muted">{{ $shortDate($submittedAt) }} {{ $time($submittedAt) }}</div>
                                <div class="gap"></div>
                                <div class="line"></div>
                                <div class="who">{{ $name($simkb->created_user) }}</div>
                                <div class="muted">(Tenant / Kontraktor)</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="sign" style="margin-top: 8px;">
                        <tr>
                            @foreach ($approvalSteps as $step)
                                @php
                                    $actor = $step->aprvresult === 'P' ? null : trim((string) ($step->lastupdate_user ?: $step->username));
                                    $names = $actor
                                        ? $name($actor)
                                        : collect(explode(',', (string) $step->username))->map(fn ($u) => $name(trim($u)))->implode(' / ');
                                    $roleName = $role($actor ?: trim(explode(',', (string) $step->username)[0]));
                                @endphp
                                <td class="signer">
                                    <div class="state">{{ $stepLabels[$step->aprvresult] ?? $step->aprvresult }}</div>
                                    <div class="muted">{{ $step->aprvresult === 'P' ? '' : $shortDate($step->aprvdateafter) . ' ' . $time($step->aprvdateafter) }}&nbsp;</div>
                                    <div class="gap"></div>
                                    <div class="line"></div>
                                    <div class="who">{{ $names }}</div>
                                    @if ($roleName)<div class="muted">({{ $roleName }})</div>@endif
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="checkbox">Check In / Out Posko FCC :
            @foreach (($checkLog ?? []) as $cl)
                <span style="font-weight: normal; margin-left: 10px;">{{ $cl->action === 'IN' ? 'In' : 'Out' }} {{ \Illuminate\Support\Carbon::parse($cl->checked_at)->format('d/m/Y H:i') }} ({{ $cl->checked_by }})</span>
            @endforeach
        </div>
    </div>
</body>
</html>
