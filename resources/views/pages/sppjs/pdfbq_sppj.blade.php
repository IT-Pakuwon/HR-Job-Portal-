<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bills of Quantities (BQ)</title>

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
            /* 🔑 VERY IMPORTANT */
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

        /* ===== WRAP FIX ===== */
        .wrap {
            word-wrap: break-word;
            white-space: normal;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER ===== -->
    <h2>{{ $title }}</h2>
    <p class="subtitle">{{ $cpny_id }} – {{ $cpny_name }}</p>

    <!-- ===== META INFO ===== -->
    <table>
        <tbody>
            <tr>
                <td class="meta-label">BQ ID</td>
                <td>{{ $bq->bqid }}</td>
                <td class="meta-label">Date</td>
                <td>{{ date('j F Y', strtotime($bq->created_at)) }}</td>
            </tr>

            <tr>
                <td class="meta-label">User</td>
                <td colspan="3">
                    {{ ucwords(strtolower(optional($bq->creator)->name)) }}
                </td>
            </tr>

            <tr>
                <td class="meta-label">SPPJ ID</td>
                <td>{{ $bq->sppjtid }}</td>
                <td class="meta-label">CS ID</td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="meta-label">Keperluan</td>
                <td colspan="3" class="wrap">
                    {{ $keperluan }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- ===== ITEMS TABLE ===== -->
    <table>
        <thead>
            <tr>
                <th style="width:4%;">No</th>
                <th style="width:26%;">Description</th>
                <th style="width:7%;">Qty</th>
                <th style="width:7%;">UOM</th>
                <th style="width:14%;">Material<br>Price</th>
                <th style="width:14%;">Total<br>Material</th>
                <th style="width:14%;">Jasa<br>Price</th>
                <th style="width:14%;">Total<br>Jasa</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($bqdetail as $item)
                <tr>
                    <td align="center">{{ $item->bq_line_no }}</td>

                    <td class="wrap">
                        {{ $item->bq_descr }}
                    </td>

                    <td align="right">
                        {{ is_null($item->qty) ? '' : number_format((float) $item->qty, 2) }}
                    </td>

                    <td align="center">
                        {{ $item->uom }}
                    </td>

                    <td align="right">
                        {{ is_null($item->est_material_price) ? '' : number_format((float) $item->est_material_price, 2) }}
                    </td>

                    <td align="right">
                        {{ is_null($item->total_est_material_price) ? '' : number_format((float) $item->total_est_material_price, 2) }}
                    </td>

                    <td align="right">
                        {{ is_null($item->est_jasa_price) ? '' : number_format((float) $item->est_jasa_price, 2) }}
                    </td>

                    <td align="right">
                        {{ is_null($item->total_est_jasa_price) ? '' : number_format((float) $item->total_est_jasa_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>


</body>

</html>
