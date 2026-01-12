<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; }

        h2 { margin: 0; font-size: 15px; text-align: center; font-weight: bold; }
        .subtitle { text-align: center; font-size: 12px; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; }
        th, td { border: 1px solid #000; padding: 5px; font-size: 10.5px; vertical-align: top; }
        th { background: #f5f5f5; font-weight: bold; text-align: center; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }

        .meta-label { width: 110px; font-weight: bold; }
        .wrap { word-wrap: break-word; white-space: normal; }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

    <h2>{{ $title }}</h2>
    <p class="subtitle">{{ $cpny_id }} – {{ $cpny_name }}</p>

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
                <td>{{ $bq->created_at ? date('j F Y', strtotime($bq->created_at)) : '' }}</td>
                <td class="meta-label">SPPJ ID</td>
                <td>{{ $bq->sppjtid }}</td>
            </tr>
            <tr>
                <td class="meta-label">Nama</td>
                <td colspan="3">{{ $vendor['name'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Alamat</td>
                <td colspan="3" class="wrap">{{ $vendor['addr'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Telp</td>
                <td>{{ $vendor['telp'] ?? '' }}</td>
                <td class="meta-label">Contact</td>
                <td>{{ $vendor['cp'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

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
                @php
                    $qty  = (float) ($item->qty ?? 0);
                    $mat  = (float) ($item->{"vendorproductprice{$idx}"} ?? 0);
                    $jasa = (float) ($item->{"vendorjasaprice{$idx}"} ?? 0);
                @endphp
                <tr>
                    <td class="center">{{ $item->bq_line_no }}</td>
                    <td class="wrap">{{ $item->bq_descr }}</td>
                    <td class="wrap">{{ $item->lokasi }}</td>
                    <td class="wrap">{{ $item->sub_lokasi }}</td>
                    <td class="right">{{ $item->qty === null ? '' : number_format($qty, 2) }}</td>
                    <td class="center">{{ $item->uom }}</td>
                    <td class="right">{{ number_format($mat, 2) }}</td>
                    <td class="right">{{ number_format($jasa, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table>
        <tbody>
            <tr>
                <td class="meta-label">GRAND TOTAL</td>
                <td class="right">{{ number_format($grandTotalMaterial ?? 0, 2) }}</td>
                <td class="right">{{ number_format($grandTotalJasa ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
