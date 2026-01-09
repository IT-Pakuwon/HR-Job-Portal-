<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CS Bills of Quantities (BQ)</title>

    <style>
        /* ===== PAGE SETUP ===== */
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        /* ===== TITLES ===== */
        h2 {
            margin: 0;
            font-size: 15px;
            text-align: center;
            font-weight: bold;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }

        /* ===== TABLE GLOBAL ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10.5px;
            vertical-align: top;
        }

        th {
            background: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        /* ===== META TABLE ===== */
        .meta-label {
            width: 110px;
            font-weight: bold;
        }

        /* ===== WRAP ===== */
        .wrap {
            word-wrap: break-word;
            white-space: normal;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <h2>Bills of Quantities (BQ)</h2>
    <p class="subtitle">AW – Artisan Wahyu, PT</p>

    <!-- ===== META INFO ===== -->
    <table>
        <tbody>
            <tr>
                <td class="meta-label">BQ ID</td>
                <td>{{ $bq->bqid }}</td>
                <td class="meta-label">CS ID</td>
                <td>{{ $bq->csid }}</td>
            </tr>
            <tr>
                <td class="meta-label">BQ Date</td>
                <td>{{ date('j F Y', strtotime($bq->created_at)) }}</td>
                <td class="meta-label">SPPJ ID</td>
                <td>{{ $bq->sppjtid }}</td>
            </tr>
            <tr>
                <td class="meta-label">Nama</td>
                <td colspan="3">{{ $bq->vendor_name }}</td>
            </tr>
            <tr>
                <td class="meta-label">Alamat</td>
                <td colspan="3" class="wrap">{{ $bq->vendor_address }}</td>
            </tr>
            <tr>
                <td class="meta-label">Telp & Fax</td>
                <td>{{ $bq->vendor_phone }}</td>
                <td class="meta-label">Contact</td>
                <td>{{ $bq->vendor_contact }}</td>
            </tr>
        </tbody>
    </table>

    <!-- ===== ITEMS TABLE ===== -->
    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:28%;">Description</th>
                <th style="width:14%;">Lokasi</th>
                <th style="width:14%;">Sub Lokasi</th>
                <th style="width:6%;">Qty</th>
                <th style="width:6%;">UOM</th>
                <th style="width:14%;">Material Price</th>
                <th style="width:14%;">Jasa Price</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($bqdetail as $item)
                <tr>
                    <td class="center">{{ $item->bq_line_no }}</td>

                    <td class="wrap">{{ $item->bq_descr }}</td>

                    <td class="wrap">{{ $item->lokasi }}</td>

                    <td class="wrap">{{ $item->sub_lokasi }}</td>

                    <td class="right">
                        {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                    </td>

                    <td class="center">{{ $item->uom }}</td>

                    <td class="right">
                        {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                    </td>

                    <td class="right">
                        {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ===== GRAND TOTAL ===== -->
    <table>
        <tbody>
            <tr>
                <td class="meta-label">GRAND TOTAL</td>
                <td class="right">
                    {{ number_format($grandTotalMaterial ?? 0, 2) }}
                </td>
                <td class="right">
                    {{ number_format($grandTotalJasa ?? 0, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

</body>

</html>
