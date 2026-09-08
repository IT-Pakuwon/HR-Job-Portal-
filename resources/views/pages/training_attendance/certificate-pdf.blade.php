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
        background: #ffffff;
    }

    .sheet {
        position: relative;
        width: 297mm;
        height: 210mm;
        background: #1b1a35;
    }

    .cert-inner {
        position: absolute;
        top: 7.7mm;
        left: 7.7mm;
        width: 281.6mm;
        height: 194.6mm;
        background: #fbf8f1;
        border: 1.5px solid #a9803f;
        color: #211f34;
    }

    .cert-content {
        text-align: center;
        padding: 37.5mm 26.7mm 0;
    }

    .brand-table {
        width: 105mm;
        margin: 0 auto;
        border-collapse: collapse;
    }
    .brand-line-cell { width: 38mm; }
    .brand-line { height: 1px; background: #cbb27a; }
    .brand-text-cell {
        white-space: nowrap;
        padding: 0 4mm;
        font-size: 12.5px;
        font-weight: bold;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #93702f;
    }

    .title {
        margin-top: 8.9mm;
        font-size: 33px;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #1b1a35;
    }

    .lede {
        margin-top: 4.7mm;
        font-size: 15px;
        color: #6d6a76;
    }

    .name {
        margin-top: 3mm;
        font-family: Georgia, "Times New Roman", serif;
        font-weight: bold;
        font-size: 40px;
        line-height: 1.1;
        color: #7c2231;
    }

    .body-text {
        margin: 6mm auto 0;
        max-width: 166mm;
        font-size: 14.5px;
        line-height: 1.55;
        color: #3c3a49;
    }
    .body-text b { color: #1b1a35; }

    .meta-line {
        margin-top: 4mm;
        font-size: 13px;
        letter-spacing: .3px;
        color: #93702f;
        font-weight: bold;
    }
    .meta-line .dot {
        margin: 0 2.4mm;
        color: #cbb27a;
    }

    .footer-row {
        margin-top: 19mm;
        width: 100%;
        table-layout: fixed;
    }
    .footer-row td { vertical-align: bottom; }

    .sign-block { text-align: left; width: 37%; }
    .sign-line {
        width: 37mm;
        height: 1px;
        background: #a39da8;
        margin-bottom: 2.4mm;
    }
    .sign-name {
        font-size: 15px;
        font-weight: bold;
        color: #211f34;
    }
    .sign-title {
        font-size: 12.5px;
        color: #7a7684;
        margin-top: .5mm;
    }

    .cert-code { text-align: center; width: 26%; }
    .cert-code .label,
    .issued-block .label {
        font-size: 10.5px;
        font-weight: bold;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: #9a95a5;
    }
    .cert-code .value {
        margin-top: 1.2mm;
        font-family: "Courier New", monospace;
        font-size: 15px;
        letter-spacing: .5px;
        color: #211f34;
    }

    .issued-block { text-align: right; width: 37%; }
    .issued-block .value {
        margin-top: 1.2mm;
        font-size: 15px;
        font-weight: bold;
        color: #211f34;
    }
</style>
</head>
<body>
<div class="sheet">
    <div class="cert-inner">
        <div class="cert-content">
            <table class="brand-table">
                <tr>
                    <td class="brand-line-cell"><div class="brand-line"></div></td>
                    <td class="brand-text-cell">{{ $companyName }}</td>
                    <td class="brand-line-cell"><div class="brand-line"></div></td>
                </tr>
            </table>

            <div class="title">Training Completion Certificate</div>
            <div class="lede">This is to certify that</div>
            <div class="name">{{ $participantName }}</div>

            <div class="body-text">has successfully completed the <b>{{ $trainingName }}</b> training program.</div>
            <div class="meta-line">
                @if (!empty($gradeName))
                    {{ $gradeName }}<span class="dot">&middot;</span>
                @endif
                Conducted {{ \Carbon\Carbon::parse($scheduleDate)->translatedFormat('d F Y') }}
            </div>

            <table class="footer-row">
                <tr>
                    <td class="sign-block">
                        <div class="sign-line"></div>
                        <div class="sign-name">Christie Natali Dewi</div>
                        <div class="sign-title">HC Dev Sr. Manager</div>
                    </td>
                    <td class="cert-code">
                        <div class="label">Certificate No.</div>
                        <div class="value">{{ $certificateNo }}</div>
                    </td>
                    <td class="issued-block">
                        <div class="label">Issued</div>
                        <div class="value">{{ \Carbon\Carbon::parse($issueDate)->translatedFormat('d F Y') }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</body>
</html>
