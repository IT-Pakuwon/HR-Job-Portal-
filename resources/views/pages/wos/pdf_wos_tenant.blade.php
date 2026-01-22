<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: top;
            padding: 2px 4px;
        }

        .header-table td {
            font-size: 12px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
        }

        .border-top {
            border-top: 1px solid #000;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .spacer {
            height: 8px;
        }

        .section {
            margin-top: 6px;
        }

        /* Info table structure */
        .info-table td {
            padding: 2px 4px;
        }

        .info-table td:first-child {
            width: 100px;
        }

        .info-table td:nth-child(2) {
            width: 10px;
        }

        /* Estimation section */
        .estimation-table td {
            padding: 3px 4px;
        }

        .estimation-table td:first-child {
            width: 260px;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        /* Signature Section */
        .signature-table {
            width: 100%;
            margin-top: 40px;
            text-align: center;
            border-collapse: collapse;
        }

        .signature-table td {
            font-size: 12px;
            vertical-align: bottom;
            padding: 6px;
        }

        .signature-table td strong {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 33%;"><strong>{{ $cpnyid }} - {{ $cpnyname }}</strong></td>
            <td style="width: 34%; text-align: center; vertical-align: middle;">
                <div class="title">{{ $title }}</div>
                <div class="subtitle">{{ $wotype }}</div>
            </td>
            <td style="width: 33%;">
                <table>
                    <tr>
                        <td>WO ID</td>
                        <td>:</td>
                        <td>{{ $docid }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>:</td>
                        <td>{{ $wodate }}</td>
                    </tr>
                    <tr>
                        <td>Status WO</td>
                        <td>:</td>
                        <td>{{ $status_doc }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer"></div>
    <div class="border-top"></div>

    <!-- Info Section -->
    <table class="info-table section">
        <tr>
            <td>User</td>
            <td>:</td>
            <td>{{ $created_by_name }}</td>
            <td>Cnpy ID</td>
            <td>:</td>
            <td>{{ $cpnyid }}</td>
            <td>Department</td>
            <td>:</td>
            <td>{{ $department_id }}</td>
        </tr>
        <tr>
            <td>Tipe</td>
            <td>:</td>
            <td>{{ $worequest }}</td>
            <td>Request</td>
            <td>:</td>
            <td>{{ $cpnyid }} - {{ $cpnyname }}</td>
            <td>PIC</td>
            <td>:</td>
            <td>{{ $picrequester }}</td>
        </tr>
        <tr>
            <td>Jenis Pekerjaan</td>
            <td>:</td>
            <td>{{ $worktype_name }}</td>
            <td>Sub Jenis Pekerjaan</td>
            <td>:</td>
            <td>{{ $subworktype_name }}</td>
            <td>Biaya WO</td>
            <td>:</td>
            <td>Rp. {{ $biaya_wo }}</td>
        </tr>
        <tr>
            <td>Lokasi</td>
            <td>:</td>
            <td>{{ $location_name }}</td>
            <td>Sub Lokasi</td>
            <td>:</td>
            <td>{{ $sub_location_name }}</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td>Info Pekerjaan</td>
            <td>:</td>
            <td colspan="7">{{ $keperluan }}</td>
        </tr>
    </table>

    <div class="border-bottom"></div>

    {{-- <!-- Estimation / Realization -->
    <table class="estimation-table section">
        <tr>
            <td>Estimasi Pekerjaan Work Order</td>
            <td>:</td>
            <td>1/1/1901</td>
            <td class="text-center">s/d</td>
            <td>1/1/1901</td>
        </tr>
        <tr>
            <td>Realisasi Pekerjaan Work Order</td>
            <td>:</td>
            <td>1/1/1901</td>
            <td class="text-center">s/d</td>
            <td>1/1/1901</td>
        </tr>
    </table>

    <div class="border-bottom"></div> --}}

    <!-- Signature Section -->
    <table style="width:100%; margin-top:50px; text-align:center; border-collapse:collapse;">
        <tr>
            <td style="width:33%; font-weight:bold;">Diminta Oleh</td>
            <td style="width:33%; font-weight:bold;">Diselesaikan Oleh</td>
            <td style="width:33%; font-weight:bold;">Dicek Oleh</td>
        </tr>

        <tr>
            <td style="height:70px;"></td>
            <td></td>
            <td></td>
        </tr>

        <tr>
            <td style="letter-spacing:3px;">(
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                )</td>
            <td style="letter-spacing:3px;">(
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                )</td>
            <td>(
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Tenant</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                )</td>
        </tr>
    </table>

</body>

</html>
