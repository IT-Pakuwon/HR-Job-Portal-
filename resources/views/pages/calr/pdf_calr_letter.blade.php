<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Cash Advance Liquidation Report' }} ({{ $doc_type ?? 'CALR' }})</title>

    <style>
        @page {
            size: 8.5in 5.5in;
            /* HALF LETTER */
            margin: 8mm 8mm 10mm 8mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5px;
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
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }

        .top-left {
            font-size: 9.5px;
            line-height: 1.2;
        }

        .doc-no {
            font-size: 10.5px;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }

        .info-wrapper {
            margin-top: 8px;
            width: 100%;
            border-collapse: collapse;
        }

        .left-info {
            width: 65%;
        }

        .right-info {
            width: 35%;
            padding-left: 8px;
        }

        .info-left-table,
        .info-right-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .info-left-table td,
        .info-right-table td {
            font-size: 9.5px;
            line-height: 1.25;
            padding: 1px 2px;
        }

        .info-label {
            width: 42%;
            white-space: nowrap;
        }

        .info-colon {
            width: 4%;
            text-align: center;
            white-space: nowrap;
        }

        .info-value {
            width: 54%;
            word-wrap: break-word;
        }

        .right-label {
            width: 45%;
            white-space: nowrap;
        }

        .right-colon {
            width: 5%;
            text-align: center;
        }

        .right-value {
            width: 50%;
        }

        .nowrap {
            white-space: nowrap;
        }

        .detail-table {
            margin-top: 8px;
            border-collapse: collapse;
            width: 100%;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 3px 4px;
            font-size: 9px;
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
            margin-top: 10px;
            border-collapse: collapse;
            font-size: 9px;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 4px;
            vertical-align: top;
        }

        .approval-table th {
            text-align: left;
            font-weight: bold;
        }

        .sig-name {
            font-weight: bold;
        }

        .sig-status {
            margin-top: 2px;
            font-weight: bold;
        }

        .sig-num {
            font-weight: bold;
            margin-right: 3px;
        }

        .blue { color: blue; }
        .red { color: red; }
        .orange { color: orange; }
        .black { color: #000; }
    </style>
</head>

<body>
    @php
        $amountRfca = (float) ($rfca_amount ?? 0);
        $amountCalr = (float) ($calr_amount ?? 0);
        $amountBalance = (float) ($balance_amount ?? 0);

        $statusColor = match (true) {
            in_array($status_doc ?? null, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc ?? null, ['Rejected', 'Cancel']) => 'red',
            in_array($status_doc ?? null, ['Hold', 'Revise', 'Revised', 'On Progress', 'Waiting Approval']) => 'orange',
            default => 'black',
        };

        $colsPerRow = 5;
        $chunks = collect($approval ?? [])->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    {{-- HEADER --}}
    <table>
        <tr>
            <td style="width: 32%;">
                <div class="top-left">
                    {{ $cpny_id ?? '' }} - {{ $cpny_name ?? '' }}<br>
                    {{ $title ?? 'Cash Advance Liquidation Report' }}<br>
                    ({{ $doc_type ?? 'CALR' }})
                </div>
            </td>

            <td style="width: 40%; text-align:center;">
                <div class="title">{{ $cpny_name ?? ($cpny_id ?? '') }}</div>
            </td>

            <td style="width: 28%;">
                <div class="doc-no">No. {{ $docid ?? '-' }}</div>
                <div class="doc-no" style="font-weight:normal;">{{ $calrdate ?? '-' }}</div>
            </td>
        </tr>
    </table>

    {{-- BODY INFO --}}
    <table class="info-wrapper">
        <tr>
            <td class="left-info">
                <table class="info-left-table">
                    <tr>
                        <td class="info-label">Dibayarkan Kpd</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $vendorname ?? '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Keperluan</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $keperluan ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            <td class="right-info">
                <table class="info-right-table">
                    <tr>
                        <td class="right-label">Total Amount</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">Rp. {{ number_format($amountRfca, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td class="right-label">Total Expenses</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">Rp. {{ number_format($amountCalr, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td class="right-label">Lebih/Kurang</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">Rp. {{ number_format($amountBalance, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- EXPENSE TABLE --}}
    <table class="detail-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 62%;">Description</th>
                <th style="width: 30%;">Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse (($details ?? collect()) as $i => $dt)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $dt->inventory_descr ?: '-' }}</td>
                    <td class="text-right">Rp. {{ number_format((float) ($dt->totalcost ?? 0), 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">No data available</td>
                </tr>
            @endforelse

            <tr>
                <td colspan="2" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>Rp. {{ number_format($amountCalr, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- APPROVAL --}}
    <table class="approval-table">
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}">
                    Status:
                    <span class="{{ $statusColor }}">{{ $status_doc ?? '-' }}</span>
                </th>
            </tr>
        </thead>

        <tbody>
            @forelse ($chunks as $rowIndex => $chunk)
                <tr>
                    @if ($rowIndex === 0)
                        <td rowspan="{{ $chunks->count() }}" style="width: 22%;">
                            <div class="sig-name">{{ $created_by_name ?? $created_by_username ?? '-' }}</div>
                            <div class="sig-status blue">Created</div>
                            <div>{{ $req_date_fmt ?? '-' }}</div>
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
                                ? \Carbon\Carbon::parse($dt2->aprv_dateafter)->format('d M Y H:i')
                                : '';
                        @endphp

                        <td>
                            <div>
                                <span class="sig-num">{{ $idx++ }}.</span>
                                <span class="sig-name">{{ $dt2->aprv_name }}</span>
                            </div>

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
                        <div class="sig-name">{{ $created_by_name ?? $created_by_username ?? '-' }}</div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt ?? '-' }}</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
