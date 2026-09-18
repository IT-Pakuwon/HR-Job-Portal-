<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pengingat Kedua - {{ $agreement->agreement_id }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 18mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.55;
            margin: 0;
            padding: 0;
        }

        .letterhead {
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 8px;
            margin-bottom: 22px;
        }

        .letterhead .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .letterhead .company-address {
            font-size: 10.5px;
            color: #444;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 16px;
        }

        .meta-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .meta-table .meta-label {
            width: 90px;
        }

        .subject-box {
            margin: 16px 0;
            text-align: center;
        }

        .subject-box .subject-title {
            display: inline-block;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            color: #991b1b;
        }

        p {
            margin: 0 0 12px;
            text-align: justify;
        }

        .addressee {
            margin-bottom: 16px;
        }

        .highlight-box {
            border: 1px solid #94a3b8;
            background: #f8fafc;
            padding: 10px 14px;
            margin: 0 0 14px;
            font-size: 11.5px;
        }

        .highlight-box td {
            padding: 2px 8px 2px 0;
            vertical-align: top;
        }

        .highlight-box .k {
            width: 150px;
            color: #334155;
            font-weight: bold;
        }

        .warning-box {
            border: 1px solid #fca5a5;
            background: #fef2f2;
            padding: 10px 14px;
            margin: 0 0 14px;
            font-size: 11.5px;
            color: #7f1d1d;
        }

        .signature-table {
            width: 100%;
            table-layout: fixed;
            margin-top: 30px;
        }

        .signature-table td {
            vertical-align: top;
        }

        .signature-space {
            height: 55px;
        }

        .pdf-footer {
            margin-top: 40px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            font-size: 9.5px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>

<body>

    @php
        $fmtDate = fn ($value) => $value ? \Carbon\Carbon::parse($value)->translatedFormat('d F Y') : '-';
        $companyAddress = $company
            ? collect([$company->address_line1, $company->address_line2, $company->city])->filter()->implode(', ')
            : '';
        $deadline = \Carbon\Carbon::parse($sentDate)->addDays(7);
    @endphp

    <div class="letterhead">
        <div class="company-name">{{ $company->cpny_name ?? $agreement->cpny_id }}</div>
        @if ($companyAddress)
            <div class="company-address">{{ $companyAddress }}</div>
        @endif
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nomor</td>
            <td>: {{ $agreement->agreement_id }}/SRT.2-LGL/{{ \Carbon\Carbon::parse($sentDate)->format('m/Y') }}</td>
            <td style="text-align:right;">{{ $company->city ?? 'Jakarta' }}, {{ \Carbon\Carbon::parse($sentDate)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Lampiran</td>
            <td colspan="2">: 1 (satu) berkas Surat Pengingat Pertama</td>
        </tr>
    </table>

    <div class="subject-box">
        <span class="subject-title">Perihal: Pengingat Kedua (Terakhir) &ndash; Pengembalian Dokumen {{ $agreement->no_psm_or_addendum ? 'PSM/Addendum' : 'Perjanjian' }}</span>
    </div>

    <div class="addressee">
        Kepada Yth.<br>
        <strong>{{ $agreement->pic_penyewa ?: $agreement->business_name }}</strong><br>
        {{ $agreement->business_name }}
        @if ($agreement->business_address)
            <br>{{ $agreement->business_address }}
        @endif
        <br>Di Tempat
    </div>

    <p>Dengan hormat,</p>

    <p>
        Merujuk pada Surat Pengingat Pertama Nomor {{ $agreement->agreement_id }}/SRT.1-LGL/{{ \Carbon\Carbon::parse($surat1SentDate)->format('m/Y') }}
        tanggal {{ $fmtDate($surat1SentDate) }} perihal pengembalian dokumen
        <strong>{{ $agreement->no_psm_or_addendum ?: '-' }}</strong> untuk {{ $agreement->business_name }}
        (salinan terlampir), hingga surat ini diterbitkan kami masih belum menerima kembali dokumen dimaksud
        dalam keadaan telah ditandatangani.
    </p>

    <div class="highlight-box">
        <table style="width:100%;">
            <tr>
                <td class="k">No. PSM / Addendum</td>
                <td>: {{ $agreement->no_psm_or_addendum ?: '-' }}</td>
            </tr>
            <tr>
                <td class="k">Tanggal Pengiriman Hardcopy</td>
                <td>: {{ $fmtDate($agreement->psm_or_addendum_delivery_date) }}</td>
            </tr>
            <tr>
                <td class="k">Tanggal Surat Pengingat Pertama</td>
                <td>: {{ $fmtDate($surat1SentDate) }}</td>
            </tr>
            <tr>
                <td class="k">Batas Pengembalian</td>
                <td>: {{ $deadline->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <p>
        Sehubungan dengan hal tersebut, ini merupakan pengingat kedua sekaligus terakhir yang kami sampaikan.
        Kami memohon kesediaan Saudara untuk mengembalikan dokumen yang telah ditandatangani kepada kami paling
        lambat 7 (tujuh) hari kalender sejak tanggal surat ini diterbitkan, yaitu selambat-lambatnya pada
        tanggal <strong>{{ $deadline->translatedFormat('d F Y') }}</strong>.
    </p>

    <div class="warning-box">
        Apabila hingga batas waktu tersebut dokumen belum kami terima, kami akan melakukan eskalasi atas
        permasalahan ini kepada pihak manajemen terkait tanpa pemberitahuan lebih lanjut.
    </div>

    <p>
        Demikian surat pengingat ini kami sampaikan. Atas perhatian dan kerja sama Saudara, kami ucapkan terima kasih.
    </p>

    <table class="signature-table">
        <tr>
            <td style="width:55%;"></td>
            <td>
                Hormat kami,<br>
                <strong>{{ $company->cpny_name ?? $agreement->cpny_id }}</strong>
                <div class="signature-space"></div>
                <strong>{{ $agreement->pic_legal ?: 'Divisi Legal' }}</strong><br>
                Divisi Legal
            </td>
        </tr>
    </table>

    <div class="pdf-footer">
        Surat ini diterbitkan secara otomatis oleh sistem Legal Agreement dan sah tanpa tanda tangan basah.
    </div>

</body>

</html>
