<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Berita Acara Serah Terima (BAST)</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        .header th {
            text-align: center;
            font-size: 16px;
        }

        .body th {
            text-align: left;
            font-weight: normal;
        }

        .body th:first-child {
            width: 50%;
        }

        .body th:last-child {
            width: 50%;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            counter-reset: item;
            font-size: 11px;
        }

        .detail-table tr.item-row {
            counter-increment: item;
        }

        .detail-table td.label,
        .detail-table td.value {
            width: 50%;
            vertical-align: top;
            padding: 2px 0;
        }

        /* nomor otomatis */
        .detail-table td.label {
            width: 25%;
            vertical-align: top;
            padding: 2px 0;
            position: relative;
            padding-left: 20px;
            /* jarak setelah nomor */
        }

        /* nomor otomatis 1., 2., dst */
        .detail-table td.label::before {
            content: counter(item) ".";
            position: absolute;
            left: 0;
        }

        /* kolom titik dua */
        .detail-table td.colon {
            width: 10px;
            /* bikin sempit supaya dekat kiri */
            vertical-align: top;
        }

        /* kolom value */
        .detail-table td.value {
            width: auto;
            vertical-align: top;
        }

        /* baris sub label (Sub Lokasi Kerja) */
        .detail-table td.label.indent {
            padding-left: 35px;
            /* sedikit menjorok */
        }

        /* sub-baris di bawah label (Sub Lokasi dsb) */
        .detail-table .indent {
            display: inline-block;
            margin-left: 15px;
        }

        /* alignment "Label : Value" */
        .field-label {
            display: inline-block;
            min-width: 90px;
            /* atur sesuai selera */
        }

        .field-colon {
            display: inline-block;
            width: 10px;
            text-align: center;
        }

        .field-value {
            display: inline-block;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <table class="header">
        <tr>
            <th>
                {{ $cpnyname }}
            </th>
        </tr>
        <tr>
            <th>Berita Acara Serah Terima</th>
        </tr>
        <tr>
            <th>
                <span class="field-label">BAST No</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $docid }}</span>
            </th>
        </tr>
    </table>

    <hr>

    {{-- Body: header SPK / CS / SPPJ --}}
    <table class="body">
        <tr>
            <th>
                <span class="field-label">SPK No</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bast->ponbr }}</span>
            </th>
            <th>
                <span class="field-label">Date</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bastdate }}</span>
            </th>
        </tr>
        <tr>
            <th>
                <span class="field-label">CS No</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bast->csid }}</span>
            </th>
            <th>
                <span class="field-label">SPPJ/T No</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bast->sppbjktid }}</span>
            </th>
        </tr>
    </table>

    {{-- Pihak pertama & kedua --}}
    <table class="body" style="margin-top: 20px">
        {{-- PIHAK PERTAMA --}}
        <tr>
            <th colspan="2">
                <span class="field-label">Nama</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $created_by_name ?? $created_by_username }}</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span class="field-label">Perusahaan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $cpnyname }}</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Selanjutnya disebut PIHAK PERTAMA</span>
            </th>
        </tr>

        {{-- PIHAK KEDUA --}}
        <tr>
            <th colspan="2">
                <span class="field-label">Nama</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bast->vendorname }}</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span class="field-label">Perusahaan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $bast->vendorname }}</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Selanjutnya disebut PIHAK KEDUA</span>
            </th>
        </tr>
    </table>

    {{-- Detail pekerjaan --}}
    <table class="detail-table">
        <tr class="item-row">
            <td class="label">
                <span>Lokasi Kerja</span><br>
                <span class="indent">Sub Lokasi Kerja</span>
            </td>
            <td class="value">
                : {{ $location_name }}<br>
                : {{ $sub_location_name }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Jenis Pekerjaan</td>
            <td class="value whitespace-pre-line break-words">
                : {{ $bast->keperluan }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Periode Pekerjaan</td>
            <td class="value">
                : {{ $startdate_fmt }} s/d {{ $enddate_fmt }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Penalty / Hari</td>
            <td class="value">
                : {{ number_format($penalty_per_day ?? 0, 0, ',', '.') }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Garansi</td>
            <td class="value">
                : {{ $spkwarranty }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Serah Terima</td>
            <td class="value">
                : {{ $handoverdate_fmt }}
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Total Penalty</td>
            <td class="value">
                : {{ number_format($total_penalty ?? 0, 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- NOTE TITLE --}}
    <table style="width:100%; border-collapse:collapse; font-size:12px;margin-top:20px;">
        <tr>
            <td style="width:60px; vertical-align:top;">Note :</td>
        </tr>
    </table>

    {{-- NOTE BOX --}}
    <div style="border:1px solid #000; padding:10px; margin-top:5px; font-size:11px; line-height:1.4;">
        <ol style="margin:0; padding-left:18px;">
            <li>
                PIHAK KEDUA telah menyerahkan pekerjaan kepada PIHAK PERTAMA dan PIHAK PERTAMA telah menerima
                dari PIHAK KEDUA seluruh hasil Pekerjaan pada tanggal penyerahan sebagaimana tercantum dalam
                Berita Acara Serah Terima (BAST) ini. Hasil pekerjaan yang dimaksud adalah sesuai dengan SPK
                sebagaimana tertulis dalam lembar BAST ini.
            </li>
            <li>
                Bahwa dengan demikian PIHAK KEDUA dinyatakan telah menyelesaikan pelaksanaan Pekerjaan dan demikian
                Pekerjaan dialihkan kembali kepada PIHAK PERTAMA.
            </li>
            <li>
                Bahwa detail mengenai Pekerjaan yang diserahkan dan garansi pekerjaan dari PIHAK PERTAMA terdapat
                pada Lampiran yang merupakan satu kesatuan yang tidak terlepaskan dari BAST ini.
            </li>
        </ol>
    </div>

    {{-- CREATED BY --}}
    <table style="width:100%; font-size:12px; line-height:1.4; border-collapse:collapse; margin-top:25px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                Dibuat Oleh &nbsp; : &nbsp;
                {{ $created_by_name ?? $created_by_username }} - {{ $req_date_fmt }}
            </td>
            <td style="width:50%;"></td>
        </tr>
    </table>

    {{-- TITLE ROW: DISERAHKAN & MENYETUJUI --}}
    <table style="width:100%; font-size:12px; text-align:center; border-collapse:collapse; margin-top:25px;">
        <tr>
            <td style="width:50%;">Diserahkan Oleh</td>
            <td style="width:50%;">Menyetujui</td>
        </tr>
    </table>

    {{-- SIGNATURE BOXES --}}
    <table style="width:100%; font-size:12px; text-align:center; border-collapse:collapse; margin-top:60px;">
        <tr>
            <td style="width:50%;">
                ( Nama dan Stempel Perusahaan )
            </td>
            <td style="width:50%;">
                ( {{ $approval_level1_name ?? ($created_by_name ?? $created_by_username) }} )
            </td>
        </tr>
    </table>

    {{-- PRINTED DATE --}}
    <table style="width:100%; font-size:11px; border-collapse:collapse; margin-top:20px;">
        <tr>
            <td style="text-align:left;">
                Printed &nbsp; : &nbsp; {{ now()->format('m/d/Y h:i:s A') }}
            </td>
        </tr>
    </table>
</body>

</html>
