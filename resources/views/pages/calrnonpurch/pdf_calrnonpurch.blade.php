<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $calr->calrnonpurchaseid }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .subtitle {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        .top-left {
            font-size: 11px;
            line-height: 1.25;
        }

        .doc-no {
            font-size: 12px;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .info-wrapper {
            margin-top: 18px;
            width: 100%;
            border-collapse: collapse;
        }

        .left-info {
            width: 68%;
        }

        .right-info {
            width: 32%;
            padding-left: 12px;
        }

        .info-left-table,
        .info-right-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-left-table td,
        .info-right-table td {
            font-size: 11px;
            line-height: 1.3;
            padding: 2px 2px;
        }

        .info-label {
            width: 42%;
            white-space: nowrap;
        }

        .info-label-long {
            width: 55%;
            white-space: nowrap;
            font-size: 10.5px;
        }

        .info-colon {
            width: 3%;
            text-align: center;
            white-space: nowrap;
        }

        .info-value {
            width: 55%;
            word-wrap: break-word;
        }

        .right-label {
            width: 43%;
            white-space: nowrap;
        }

        .right-colon {
            width: 5%;
            text-align: center;
        }

        .right-value {
            width: 52%;
        }

        .nowrap {
            white-space: nowrap;
        }

        .amount-summary {
            margin-top: 12px;
            width: 100%;
        }

        .amount-summary td {
            font-size: 11px;
            padding: 3px 2px;
        }

        .detail-table {
            margin-top: 14px;
            border-collapse: collapse;
            width: 100%;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10.5px;
        }

        .detail-table th {
            background: #f7f7f7;
            font-weight: bold;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .approval-table {
            margin-top: 18px;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }

        .approval-table th {
            text-align: left;
            font-weight: bold;
        }

        .sig-name {
            font-weight: bold;
            text-transform: uppercase;
        }

        .sig-status {
            margin-top: 3px;
            font-weight: bold;
        }

        .blue { color: blue; }
        .red { color: red; }
        .orange { color: orange; }
        .black { color: #000; }
    </style>
</head>

<body>
    @php
        $fmtDate = function ($date, $format = 'd M Y') {
            if (empty($date)) {
                return '-';
            }

            try {
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Throwable $e) {
                return $date;
            }
        };

        $amountRfp = (float) ($calr->amountrfp ?? 0);
        $amountSettlement = (float) ($calr->amountsettlement ?? 0);
        $amountDiff = (float) ($calr->amountdiff ?? ($amountRfp - $amountSettlement));

        $statusText = match ($calr->status) {
            'P' => 'On Progress',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
            'X' => 'Cancel',
            default => 'Unknown',
        };

        $statusColor = match ($calr->status) {
            'C' => 'blue',
            'R', 'X' => 'red',
            'D', 'P' => 'orange',
            default => 'black',
        };

        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = $approval->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    {{-- HEADER --}}
    <table>
        <tr>
            <td style="width: 30%;">
                <div class="top-left">
                    {{ $cpny_name ?: $calr->cpny_id }}<br>
                    CASH ADVANCE LIQUIDATION REPORT<br>
                    (CALR NON PURCHASE DAS)
                </div>
            </td>

            <td style="width: 45%; text-align:center;">
                <div class="title">{{ $cpny_name ?: $calr->cpny_id }}</div>
            </td>

            <td style="width: 25%;">
                <div class="doc-no">No. {{ $calr->calrnonpurchaseid }}</div>
            </td>
        </tr>
    </table>

    {{-- BODY INFO --}}
    <table class="info-wrapper">
        <tr>
            <td class="left-info">
                <table class="info-left-table">
                    <tr>
                        <td class="info-label">RCA No</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->rfpnonpurchaseid }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Requester</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->user_peminta ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Department</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->department_id ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Keperluan/Purpose</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->keperluan ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label-long">Tgl. Batas Penyelesaian</td>
                        <td class="info-colon">:</td>
                        <td class="info-value nowrap">
                            {{ $fmtDate($calr->datebataspenyelesaian, 'd M Y') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="info-label">Bentuk Pembayaran</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->paymenttype ?: '-' }}</td>
                    </tr>
                </table>
            </td>

            <td class="right-info">
                <table class="info-right-table">
                    <tr>
                        <td class="right-label">Tgl/Date</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">
                            {{ $fmtDate($calr->calrnonpurchasedate, 'd M Y') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="right-label">Settlement</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">
                            Rp. {{ number_format($amountSettlement, 0, ',', '.') }}
                        </td>
                    </tr>

                    <tr>
                        <td class="right-label">Terbilang</td>
                        <td class="right-colon">:</td>
                        <td class="right-value">
                            {{ $calr->terbilang }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- AMOUNT SUMMARY --}}
    <table class="amount-summary">
        <tr>
            <td style="width: 25%;">Amount RCA</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">Rp. {{ number_format($amountRfp, 0, ',', '.') }}</td>

            <td style="width: 25%;">Amount Settlement</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">Rp. {{ number_format($amountSettlement, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td>Lebih/Kurang</td>
            <td>:</td>
            <td colspan="4">Rp. {{ number_format($amountDiff, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- DETAIL --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 67%;">Description</th>
                <th style="width: 25%;">Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($details as $i => $dt)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $dt->keperluan_detail }}</td>
                    <td class="text-right">
                        {{ number_format((float) ($dt->amount_request_penyelesaian ?? 0), 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No data available</td>
                </tr>
            @endforelse

            <tr>
                <td colspan="2" class="text-right"><strong>Total</strong></td>
                <td class="text-right">
                    <strong>{{ number_format($amountSettlement, 0, ',', '.') }}</strong>
                </td>
            </tr>
        </tbody>
    </table>

    {{-- FINANCE FLOW --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th colspan="6" style="text-align:left;">Finance & Treasury</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td style="width: 18%;">Finance</td>
                <td style="width: 2%;">:</td>
                <td style="width: 30%; color: {{ $calr->statusreceive === 'C' ? 'blue' : 'orange' }};">
                    {{ $calr->statusreceive === 'C' ? 'Received' : 'Pending' }}
                </td>

                <td style="width: 18%;">Treasury</td>
                <td style="width: 2%;">:</td>
                <td style="width: 30%; color: {{ $calr->statuspayment === 'C' ? 'blue' : 'orange' }};">
                    {{ $calr->statuspayment === 'C' ? 'Received' : 'Pending' }}
                </td>
            </tr>

            <tr>
                <td>User Finance</td>
                <td>:</td>
                <td>{{ $calr->userreceive ?: '-' }}</td>

                <td>User Treasury</td>
                <td>:</td>
                <td>{{ $calr->userpayment ?: '-' }}</td>
            </tr>

            <tr>
                <td>Date Finance</td>
                <td>:</td>
                <td>{{ $calr->receivedate ? $fmtDate($calr->receivedate, 'd M Y H:i') : '-' }}</td>

                <td>Date Treasury</td>
                <td>:</td>
                <td>{{ $calr->paymentdate ? $fmtDate($calr->paymentdate, 'd M Y H:i') : '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- APPROVAL --}}
    <table class="approval-table">
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}">
                    Status:
                    <span class="{{ $statusColor }}">{{ $statusText }}</span>
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($chunks as $rowIndex => $chunk)
                <tr>
                    @if ($rowIndex === 0)
                        <td rowspan="{{ $chunks->count() }}" style="width: 25%;">
                            <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
                            <div class="sig-status blue">Created</div>
                            <div>{{ $req_date_fmt }}</div>
                        </td>
                    @endif

                    @foreach ($chunk as $dt2)
                        @php
                            $label = match ($dt2->status) {
                                'A' => 'Approved',
                                'R' => 'Rejected',
                                'P' => 'Waiting',
                                'D' => 'Revised',
                                default => 'Waiting',
                            };

                            $color = match ($dt2->status) {
                                'A' => 'blue',
                                'R' => 'red',
                                'P' => 'orange',
                                'D' => 'red',
                                default => 'orange',
                            };

                            $dateStr = $dt2->aprv_dateafter
                                ? \Carbon\Carbon::parse($dt2->aprv_dateafter)->format('Y-m-d H:i:s')
                                : '';
                        @endphp

                        <td>
                            <div class="sig-name">{{ $idx++ }}. {{ $dt2->aprv_name }}</div>
                            <div class="sig-status {{ $color }}">{{ $label }}</div>
                            <div>{{ $dateStr }}</div>
                        </td>
                    @endforeach

                    @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                        <td>&nbsp;</td>
                    @endfor
                </tr>
            @empty
                <tr>
                    <td>
                        <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt }}</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>