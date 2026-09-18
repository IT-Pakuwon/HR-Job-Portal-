<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Surat Pengingat Pertama - {{ $agreement->agreement_id }}</title>
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

        ol {
            margin: 0 0 14px 18px;
            padding: 0;
        }

        ol li {
            margin-bottom: 6px;
            text-align: justify;
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
            <td>: {{ $agreement->agreement_id }}/SRT.1-LGL/{{ \Carbon\Carbon::parse($sentDate)->format('m/Y') }}</td>
            <td style="text-align:right;">{{ $company->city ?? 'Jakarta' }}, {{ \Carbon\Carbon::parse($sentDate)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Lampiran</td>
            <td colspan="2">: 1 (satu) berkas Tanda Terima Pengiriman Dokumen</td>
        </tr>
    </table>

    <div class="subject-box">
        <span class="subject-title">Perihal: Pengingat Pertama &ndash; Pengembalian Dokumen {{ $agreement->no_psm_or_addendum ? 'PSM/Addendum' : 'Perjanjian' }}</span>
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
        Sehubungan dengan pengiriman hardcopy dokumen
        <strong>{{ $agreement->no_psm_or_addendum ?: '-' }}</strong>
        tanggal {{ $fmtDate($agreement->psm_or_addendum_date) }}
        untuk {{ $agreement->business_name }}
        yang telah kami kirimkan kepada Saudara pada tanggal
        <strong>{{ $fmtDate($agreement->psm_or_addendum_delivery_date) }}</strong>
        (sebagaimana tercantum pada Tanda Terima terlampir), hingga surat ini diterbitkan kami belum menerima
        kembali dokumen dimaksud dalam keadaan telah ditandatangani.
    </p>

    <div class="highlight-box">
        <table style="width:100%;">
            <tr>
                <td class="k">No. PSM / Addendum</td>
                <td>: {{ $agreement->no_psm_or_addendum ?: '-' }}</td>
            </tr>
            <tr>
                <td class="k">Tanggal Dokumen</td>
                <td>: {{ $fmtDate($agreement->psm_or_addendum_date) }}</td>
            </tr>
            <tr>
                <td class="k">Tanggal Pengiriman Hardcopy</td>
                <td>: {{ $fmtDate($agreement->psm_or_addendum_delivery_date) }}</td>
            </tr>
            <tr>
                <td class="k">Batas Pengembalian</td>
                <td>: {{ \Carbon\Carbon::parse($sentDate)->addDays(14)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <p>
        Sehubungan dengan hal tersebut, kami memohon kesediaan Saudara untuk mengembalikan dokumen yang telah
        ditandatangani kepada kami paling lambat 14 (empat belas) hari kalender sejak tanggal surat ini diterbitkan,
        yaitu selambat-lambatnya pada tanggal <strong>{{ \Carbon\Carbon::parse($sentDate)->addDays(14)->translatedFormat('d F Y') }}</strong>.
    </p>

    <p>
        Apabila hingga batas waktu tersebut dokumen belum kami terima, kami akan menerbitkan Surat Pengingat Kedua
        sebagai tindak lanjut atas hal ini.
    </p>

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
