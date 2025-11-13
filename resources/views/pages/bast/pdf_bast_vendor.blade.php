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
            position: relative;
            padding-left: 20px;
            /* jarak teks setelah nomor */
        }

        .detail-table td.label::before {
            content: counter(item) ".";
            position: absolute;
            left: 0;
        }

        /* sub-baris di bawah label (Sub Lokasi dsb) */
        .detail-table .indent {
            display: inline-block;
            margin-left: 15px;
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
                <span>BAST No : {{ $docid }}</span>
            </th>
        </tr>
    </table>
    <hr>
    {{-- Body --}}
    <table class="body">
        <tr>
            <th>
                <span>SPK No :</span>
                <span>8000006261</span>
            </th>
            <th>
                <span>Date :</span>
                <span>{{ $bastdate }}</span>
            </th>
        </tr>
        <tr>
            <th>
                <span>CS No :</span>
                <span>CS25100100</span>
            </th>
            <th>
                <span>SPPJ/T No :</span>
                <span>PJ25100024</span>
            </th>
        </tr>
    </table>

    <table class="body" style="margin-top: 20px">
        <tr>
            <th colspan="2">
                <span>Nama :</span>
                <span>Molyvia</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Perusahaan :</span>
                <span>Molyvia</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Selanjutnya disebut PIHAK PERTAMA</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Nama :</span>
                <span>Molyvia</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Perusahaan :</span>
                <span>PRASADHA PAMUNAH LIMBAH INDUSTRI, PT</span>
            </th>
        </tr>
        <tr>
            <th colspan="2">
                <span>Selanjutnya disebut PIHAK KEDUA</span>
            </th>
        </tr>
    </table>

    <table class="detail-table">
        <tr class="item-row">
            <td class="label">
                <span>Lokasi Kerja</span><br>
                <span class="indent">Sub Lokasi Kerja</span>
            </td>
            <td class="value">
                : LANTAI B2<br>
                : RUANG LIMBAH B3
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Jenis Pekerjaan</td>
            <td class="value">
                : Pengangkutan Limbah B3 Gandaria City Superblok tahun 2025
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Periode Pekerjaan</td>
            <td class="value">: 9/27/2025 s/d 12/12/2025</td>
        </tr>

        <tr class="item-row">
            <td class="label">Penalty / Hari</td>
            <td class="value">: 250,000.00</td>
        </tr>

        <tr class="item-row">
            <td class="label">Garansi</td>
            <td class="value">
                : Terhitung dari Tanggal Serah Terima Pekerjaan dan disetujui
                BAST ini oleh kedua belah pihak.
            </td>
        </tr>

        <tr class="item-row">
            <td class="label">Serah Terima</td>
            <td class="value">: 11/3/2025 10:12 AM</td>
        </tr>

        <tr class="item-row">
            <td class="label">Total Penalty</td>
            <td class="value">: 0.00</td>
        </tr>
    </table>

    <!-- NOTE TITLE -->
    <table style="width:100%; border-collapse:collapse; font-size:12px;margin-top:20px;">
        <tr>
            <td style="width:60px; vertical-align:top;">Note :</td>
        </tr>
    </table>

    <!-- NOTE BOX -->
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

    <!-- CREATED BY -->
    <table style="width:100%; font-size:12px; line-height:1.4; border-collapse:collapse; margin-top:25px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                Dibuat Oleh Purchasing &nbsp; : &nbsp; Andre Febriadi - 10 November 2025
            </td>
            <td style="width:50%;"></td>
        </tr>
    </table>

    <!-- TITLE ROW: DISERAHKAN & MENYETUJUI -->
    <table style="width:100%; font-size:12px; text-align:center;  border-collapse:collapse; margin-top:25px;">
        <tr>
            <td style="width:50%;">Diserahkan Oleh</td>
            <td style="width:50%;">Menyetujui</td>
        </tr>
    </table>

    <!-- SIGNATURE BOXES -->
    <table style="width:100%; font-size:12px; text-align:center; border-collapse:collapse; margin-top:60px;">
        <tr>
            <td style="width:50%;">
                ( Nama dan Stempel Perusahaan )
            </td>
            <td style="width:50%;">
                ( Molyvia )
            </td>
        </tr>
    </table>

    <!-- PRINTED DATE -->
    <table style="width:100%; font-size:11px; border-collapse:collapse; margin-top:20px;">
        <tr>
            <td style="text-align:left;">
                Printed &nbsp; : &nbsp; 11/13/2025 8:59:14 AM
            </td>
        </tr>
    </table>
</body>

</html>
