<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $rfpnonpurch->rfpnonpurchaseid }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 12mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 15px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .doc-no {
            font-size: 16px;
            font-weight: bold;
            white-space: nowrap;
        }

        .left-company {
            font-size: 15px;
            line-height: 1.15;
        }

        .info-wrapper {
            margin-top: 24px;
            width: 100%;
            border-collapse: collapse;
        }

        .info-wrapper td {
            padding: 0;
            vertical-align: top;
        }

        .left-info {
            width: 67%;
        }

        .right-info {
            width: 33%;
            padding-left: 14px !important;
        }

        .info-left-table,
        .info-right-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-left-table td,
        .info-right-table td {
            font-size: 14px;
            line-height: 1.25;
            padding: 2px 2px;
            vertical-align: top;
        }

        .info-label {
            width: 45%;
            white-space: nowrap;
        }

        .info-label-long {
            width: 62%;
            white-space: nowrap;
            font-size: 13px;
        }

        .info-colon {
            width: 3%;
            text-align: center;
            white-space: nowrap;
        }

        .info-value {
            width: 52%;
        }

        .right-label {
            width: 38%;
            white-space: nowrap;
        }

        .right-colon {
            width: 5%;
            text-align: center;
            white-space: nowrap;
        }

        .right-value {
            width: 57%;
        }

        .nowrap {
            white-space: nowrap;
        }

        .approval-table {
            margin-top: 88px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 7px;
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
            margin-top: 4px;
            font-weight: bold;
        }

        .blue {
            color: blue;
        }

        .red {
            color: red;
        }

        .orange {
            color: orange;
        }

        .black {
            color: #000;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>

<body>
    @php
        $type = strtoupper(trim((string) $rfpnonpurch->rfpnonpurchase_type));

        $docTitle = $type === 'RCA'
            ? 'REQUEST FOR CASH ADVANCE'
            : 'REQUEST FOR PAYMENT';

        $docSubtitle = $type === 'RCA'
            ? '(RCA NON PURCHASE)'
            : '(RFP NON PURCHASE)';

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

        $amount = (float) ($rfpnonpurch->amountrequestpayment ?? 0);

        $statusText = match ($rfpnonpurch->status) {
            'P' => 'On Progress',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
            'X' => 'Cancel',
            default => 'Unknown',
        };

        $statusColor = match ($rfpnonpurch->status) {
            'C' => 'blue',
            'R', 'X' => 'red',
            'D', 'P' => 'orange',
            default => 'black',
        };

        $kepadaText = $rfpnonpurch->imnonpurchase_kepada ?: 'TREASURY';
        $paymentText = $rfpnonpurch->paymenttype ?: $rfpnonpurch->payment_type ?? '';

        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = $approval->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    {{-- HEADER --}}
    <table>
        <tr>
            <td style="width: 31%;">
                <div class="left-company">
                    {{ $cpny_name ?: $rfpnonpurch->cpny_id }}
                </div>

                <div class="left-title">
                    {{ $docTitle }}<br>
                    {{ $docSubtitle }}
                </div>
            </td>

            <td style="width: 44%; text-align: center;">
                <div class="header-title">
                    {{ $cpny_name ?: $rfpnonpurch->cpny_id }}
                </div>
            </td>

            <td style="width: 25%; text-align: left;">
                <div class="doc-no">
                    No. {{ $rfpnonpurch->rfpnonpurchaseid }}
                </div>
            </td>
        </tr>
    </table><br>

    {{-- BODY INFO --}}
    <table class="info-table">
        <tr>
            <td style="width: 31%;" class="label">Kepada/to</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 30%;">{{ $kepadaText }}</td>

            <td style="width: 15%;" class="label">Tgl/Date</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 20%;" class="nowrap">{{ $fmtDate($rfpnonpurch->rfpnonpurchasedate, 'd M Y') }}</td>
        </tr>

        <tr>
            <td class="label">Dibayarkan kpd/Please Pay to</td>
            <td class="colon">:</td>
            <td>{{ $rfpnonpurch->pleasepayto ?: '-' }}</td>

            <td class="label">Jumlah/Amount</td>
            <td class="colon">:</td>
            <td class="nowrap">Rp. {{ number_format($amount, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="label">Terbilang/Amount in Words</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfpnonpurch->terbilang }}</td>
        </tr>

        <tr>
            <td class="label">Keperluan/Purpose</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfpnonpurch->keperluan ?: '-' }}</td>
        </tr>

        <tr>
            <td style="width: 32%;" class="label">
                Tgl. Diperlukan/Date needed (WAJIB DIISI)
            </td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 66%;" colspan="4" class="nowrap">
                {{ $fmtDate($rfpnonpurch->datediperlukan, 'd M Y') }}
            </td>
        </tr>

        <tr>
            <td style="width: 32%;" class="label">
                Bentuk Pembayaran/Form Of Payment
            </td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 66%;" colspan="4" class="nowrap">
                {{ $paymentText }}
            </td>
        </tr>
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
                            <div>
                                <span class="sig-name">{{ $idx++ }}. {{ $dt2->aprv_name }}</span>
                            </div>

                            <div class="sig-status {{ $color }}">
                                {{ $label }}
                            </div>

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