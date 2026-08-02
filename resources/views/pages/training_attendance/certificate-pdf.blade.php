<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Certificate — {{ $participantName }}</title>
<style>
    @page {
        size: A4 landscape;
        margin: 0;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        color: #1e293b;
        background: #ffffff;
    }

    .sheet {
        position: relative;
        width: 297mm;
        height: 210mm;
        padding: 10mm;
    }

    .frame-outer {
        position: absolute;
        top: 8mm;
        left: 8mm;
        right: 8mm;
        bottom: 8mm;
        border: 2px solid #1e1b4b;
    }

    .frame-inner {
        position: absolute;
        top: 10.5mm;
        left: 10.5mm;
        right: 10.5mm;
        bottom: 10.5mm;
        border: 1px solid #c9a24b;
    }

    .corner {
        position: absolute;
        width: 16mm;
        height: 16mm;
        border-color: #c9a24b;
    }
    .corner-tl { top: 8mm; left: 8mm; border-top: 3px solid #c9a24b; border-left: 3px solid #c9a24b; }
    .corner-tr { top: 8mm; right: 8mm; border-top: 3px solid #c9a24b; border-right: 3px solid #c9a24b; }
    .corner-bl { bottom: 8mm; left: 8mm; border-bottom: 3px solid #c9a24b; border-left: 3px solid #c9a24b; }
    .corner-br { bottom: 8mm; right: 8mm; border-bottom: 3px solid #c9a24b; border-right: 3px solid #c9a24b; }

    .content {
        position: relative;
        width: 100%;
        height: 100%;
        padding: 20mm 26mm 14mm;
        text-align: center;
    }

    .brand-logo { height: 15mm; margin-bottom: 3mm; }

    .brand-name {
        font-size: 10.5px;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #1e1b4b;
    }

    .rule {
        width: 60mm;
        height: 1px;
        background-color: #c9a24b;
        margin: 5mm auto;
    }

    .cert-title {
        font-family: Georgia, "Times New Roman", serif;
        font-size: 30px;
        font-weight: bold;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #1e1b4b;
    }

    .cert-subtitle {
        margin-top: 3mm;
        font-size: 11px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #94a3b8;
    }

    .preamble {
        margin-top: 10mm;
        font-size: 12px;
        font-style: italic;
        color: #475569;
    }

    .participant-name {
        margin-top: 4mm;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 30px;
        font-weight: bold;
        color: #0f172a;
        display: inline-block;
        padding-bottom: 3mm;
        border-bottom: 1.5px solid #c9a24b;
    }

    .body-text {
        margin-top: 7mm;
        font-size: 12px;
        color: #475569;
        line-height: 1.7;
    }

    .training-name {
        margin-top: 2mm;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 18px;
        font-weight: bold;
        color: #1e1b4b;
    }

    .meta-row {
        margin-top: 3mm;
        font-size: 11px;
        color: #64748b;
    }

    .meta-row .badge {
        display: inline-block;
        padding: 1.5mm 4mm;
        border: 1px solid #c9a24b;
        border-radius: 3mm;
        color: #92702f;
        font-size: 10px;
        font-weight: bold;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-right: 3mm;
    }

    .footer-table {
        position: absolute;
        left: 26mm;
        right: 26mm;
        bottom: 16mm;
        width: calc(100% - 52mm);
        table-layout: fixed;
    }

    .footer-table td {
        vertical-align: bottom;
        font-size: 10px;
        color: #64748b;
    }

    .footer-left { text-align: left; }
    .footer-right { text-align: center; width: 60mm; }

    .footer-left .label {
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 8.5px;
        color: #94a3b8;
    }
    .footer-left .value {
        margin-top: 1mm;
        font-size: 11px;
        font-weight: bold;
        color: #1e293b;
    }

    .sign-line {
        border-top: 1px solid #94a3b8;
        margin-bottom: 2mm;
        padding-top: 2mm;
    }
    .sign-name {
        font-size: 11px;
        font-weight: bold;
        color: #1e293b;
    }
    .sign-title {
        font-size: 9.5px;
        color: #64748b;
    }
</style>
</head>
<body>
<div class="sheet">
    <div class="frame-outer"></div>
    <div class="frame-inner"></div>
    <div class="corner corner-tl"></div>
    <div class="corner corner-tr"></div>
    <div class="corner corner-bl"></div>
    <div class="corner corner-br"></div>

    <div class="content">
        @if ($logo)
            @php
                $logoFile = match ($logo) {
                    'AW' => 'gc.png',
                    'EP' => 'kk.png',
                    'PSA' => 'bm.png',
                    'GPS' => 'pmb.png',
                    'PRB' => 'prb.png',
                    'GH' => 'gh.png',
                    default => null,
                };
            @endphp
            @if ($logoFile && file_exists(public_path("logo/{$logoFile}")))
                <img class="brand-logo" src="{{ public_path("logo/{$logoFile}") }}" alt="{{ $companyName }}">
            @endif
        @endif
        <div class="brand-name">{{ $companyName }}</div>

        <div class="rule"></div>

        <div class="cert-title">Certificate</div>
        <div class="cert-subtitle">of Training Completion</div>

        <div class="preamble">This certificate is proudly presented to</div>

        <div class="participant-name">{{ $participantName }}</div>

        <div class="body-text">for successfully completing the training program</div>
        <div class="training-name">{{ $trainingName }}</div>

        <div class="meta-row">
            @if (!empty($gradeName))
                <span class="badge">Level: {{ $gradeName }}</span>
            @endif
            <span>Conducted on {{ \Carbon\Carbon::parse($scheduleDate)->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <table class="footer-table">
        <tr>
            <td class="footer-left">
                <div class="label">Certificate No.</div>
                <div class="value">{{ $certificateNo }}</div>
                <div class="label" style="margin-top:3mm;">Issued</div>
                <div class="value">{{ \Carbon\Carbon::parse($issueDate)->translatedFormat('d F Y') }}</div>
            </td>
            <td class="footer-right">
                <div class="sign-line">
                    <div class="sign-name">Christie Natali Dewi</div>
                    <div class="sign-title">HC Dev Sr. Manager</div>
                </div>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
