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

        $vendorCount = count($vendors);

        $sumMat = [];
        $sumJsa = [];
        foreach ($vendors as $v) {
            $sumMat[$v['idx']] = 0;
            $sumJsa[$v['idx']] = 0;
        }
    @endphp

    <div class="header">
        <div class="title">ATTACHMENT</div>
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
                <th rowspan="3" style="width:30px vertical-align: middle;">No</th>
                <th rowspan="3" style="vertical-align: middle;">Description</th>
                <th rowspan="3" style="width:40px vertical-align: middle;">Qty</th>
                <th rowspan="3" style="width:50px vertical-align: middle;">UOM</th>
                @foreach ($vendors as $v)
                    <th colspan="4">{{ strtoupper($v['vendorname']) }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($vendors as $v)
                    <th colspan="2">Material</th>
                    <th colspan="2">Jasa</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($vendors as $v)
                    <th style="width:85px">Unit Price</th>
                    <th style="width:95px">Total Price</th>
                    <th style="width:85px">Unit Price</th>
                    <th style="width:95px">Total Price</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @php $no = 1; @endphp

            @foreach ($details as $d)
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $d->bq_descr ?? '-' }}</td>
                    <td class="text-center">{{ number_format((float) ($d->qty ?? 0), 2, ',', '.') }}</td>
                    <td class="text-center">{{ $d->uom ?? '-' }}</td>

                    @foreach ($vendors as $v)
                        @php
                            $i = $v['idx'];
                            $qty = (float) ($d->qty ?? 0);

                            $matUnit = (float) ($d->{"vendorproductprice{$i}"} ?? 0);
                            $jsaUnit = (float) ($d->{"vendorjasaprice{$i}"} ?? 0);

                            $matTotal = $qty * $matUnit;
                            $jsaTotal = $qty * $jsaUnit;

                            $sumMat[$i] += $matTotal;
                            $sumJsa[$i] += $jsaTotal;
                        @endphp

                        <td class="text-right">{{ rp($matUnit) }}</td>
                        <td class="text-right">{{ rp($matTotal) }}</td>
                        <td class="text-right">{{ rp($jsaUnit) }}</td>
                        <td class="text-right">{{ rp($jsaTotal) }}</td>
                    @endforeach
                </tr>
            @endforeach

            {{-- SUB TOTAL --}}
            <tr class="summary">
                <td colspan="4" class="text-right">Sub Total</td>
                @foreach ($vendors as $v)
                    @php $i = $v['idx']; @endphp
                    <td colspan="2" class="text-right">{{ rp($sumMat[$i]) }}</td>
                    <td colspan="2" class="text-right">{{ rp($sumJsa[$i]) }}</td>
                @endforeach
            </tr>

            {{-- TOTAL / PPN / GRAND --}}
            @php
                $grandAll = 0;
            @endphp

            <tr class="summary">
                <td colspan="{{ 4 + $vendorCount * 4 - 1 }}" class="text-right">Total</td>
                <td class="text-right">
                    @php
                        $totalAll = array_sum($sumMat) + array_sum($sumJsa);
                    @endphp
                    {{ rp($totalAll) }}
                </td>
            </tr>

            <tr class="summary">
                <td colspan="{{ 4 + $vendorCount * 4 - 1 }}" class="text-right">PPN 11%</td>
                <td class="text-right">
                    @php
                        $ppn = round($totalAll * 0.11);
                    @endphp
                    {{ rp($ppn) }}
                </td>
            </tr>

            <tr class="summary">
                <td colspan="{{ 4 + $vendorCount * 4 - 1 }}" class="text-right">Grand Total</td>
                <td class="text-right">
                    {{ rp($totalAll + $ppn) }}
                </td>
            </tr>
        </tbody>
    </table>

    <table style="width:100%; margin-top:40px; border:none;">
        <tr>
            <td style="text-align:right; border:none;">
                <div style="margin-bottom:60px;">
                    Tanda Tangan dan Stempel
                </div>
                <div>
                    (..........................................)
                </div>
            </td>
        </tr>
    </table>



</body>

</html>
