<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BQ SPK {{ $po->ponbr }}</title>

    <style>
        @page {
            margin: 18mm;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10px;
            color: #000;
        }

        .header {
            text-align: center;
            font-weight: bold;
            line-height: 1.4;
            margin-bottom: 12px;
        }

        .header .title {
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 0.8px solid #444;
            padding: 4px 5px;
            vertical-align: middle;
        }

        th {
            text-align: center;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .summary td {
            font-weight: bold;
        }
    </style>
</head>

<body>

    @php
        function rp($n)
        {
            return (float) $n === 0.0 ? '-' : 'Rp ' . number_format((float) $n, 0, ',', '.');
        }

        // ===== VENDOR (PASTI 1) =====
        $vidx = null;
        $vendorName = '';

        for ($i = 1; $i <= 6; $i++) {
            if (($cs->{"vendorid{$i}"} ?? null) === $po->vendorid) {
                $vidx = $i;
                $vendorName = $cs->{"vendorname{$i}"} ?? '';
                break;
            }
        }

        $sumMat = 0;
        $sumJsa = 0;
    @endphp

    <div class="header">
        <div class="title">ATTACHMENT</div>
        @if($businessUnit)
            <div style="font-size:12px;">
                {{ $businessUnit->business_unit_name }}
            </div>
        @endif
    </div>

    <table style="width:100%; margin-bottom:12px;">
        <tr>
            <td style="width:20%; font-weight:bold;">SPK</td>
            <td style="width:30%;">{{ $po->ponbr }}</td>
            <td style="width:20%; font-weight:bold;">SPPJ / SPPT NO.</td>
            <td style="width:30%;">{{ $cs->sppbjktid ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Keperluan</td>
            <td colspan="3">{{ strtoupper($cs->keperluan ?? 'PEKERJAAN') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th rowspan="3" style="width:30px;">No</th>
                <th rowspan="3">Description</th>
                <th rowspan="3" style="width:40px;">Qty</th>
                <th rowspan="3" style="width:50px;">UOM</th>
                <th colspan="4">{{ strtoupper($vendorName ?: 'VENDOR') }}</th>
            </tr>
            <tr>
                <th colspan="2">Material</th>
                <th colspan="2">Jasa</th>
            </tr>
            <tr>
                <th style="width:85px;">Unit Price</th>
                <th style="width:95px;">Total Price</th>
                <th style="width:85px;">Unit Price</th>
                <th style="width:95px;">Total Price</th>
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp

            @foreach ($details as $d)
                @php
                    $qty = (float) ($d->qty ?? 0);

                    $matUnit = (float) ($d->{"vendorproductprice{$vidx}"} ?? 0);
                    $jsaUnit = (float) ($d->{"vendorjasaprice{$vidx}"} ?? 0);

                    $matTotal = $qty * $matUnit;
                    $jsaTotal = $qty * $jsaUnit;

                    $sumMat += $matTotal;
                    $sumJsa += $jsaTotal;
                @endphp

                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $d->bq_descr ?? '-' }}</td>
                    <td class="text-center">{{ number_format($qty, 2, ',', '.') }}</td>
                    <td class="text-center">{{ $d->uom ?? '-' }}</td>

                    <td class="text-right">{{ rp($matUnit) }}</td>
                    <td class="text-right">{{ rp($matTotal) }}</td>
                    <td class="text-right">{{ rp($jsaUnit) }}</td>
                    <td class="text-right">{{ rp($jsaTotal) }}</td>
                </tr>
            @endforeach

            {{-- SUB TOTAL --}}
            <tr class="summary">
                <td colspan="4" class="text-right">Sub Total</td>
                <td colspan="2" class="text-right">{{ rp($sumMat) }}</td>
                <td colspan="2" class="text-right">{{ rp($sumJsa) }}</td>
            </tr>

            @php
                $total = $sumMat + $sumJsa;
                $ppn = round($total * 0.11);
                $grand = $total + $ppn;
            @endphp

            <tr class="summary">
                <td colspan="7" class="text-right">Total</td>
                <td class="text-right">{{ rp($total) }}</td>
            </tr>

            <tr class="summary">
                <td colspan="7" class="text-right">PPN 11%</td>
                <td class="text-right">{{ rp($ppn) }}</td>
            </tr>

            <tr class="summary">
                <td colspan="7" class="text-right">Grand Total</td>
                <td class="text-right">{{ rp($grand) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="width:100%; margin-top:40px; border:none;">
        <tr>
            <td style="text-align:right; border:none;">
                <div style="margin-bottom:60px;">Tanda Tangan dan Stempel</div>
                <div>(..........................................)</div>
            </td>
        </tr>
    </table>

</body>

</html>
