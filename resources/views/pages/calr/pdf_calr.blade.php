<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $docid }}</title>

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
        }

        .sig-status {
            margin-top: 3px;
            font-weight: bold;
        }

        .sig-num {
            font-weight: bold;
            margin-right: 4px;
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

        $amountRfca = (float) ($rfca_amount ?? 0);
        $amountCalr = (float) ($calr_amount ?? 0);
        $amountBalance = (float) ($balance_amount ?? 0);

        $statusColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            in_array($status_doc, ['Hold', 'Revise', 'Revised', 'On Progress', 'Waiting Approval']) => 'orange',
            default => 'black',
        };

        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = collect($approval ?? [])->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    {{-- HEADER --}}
    <table>
        <tr>
            <td style="width: 30%;">
                <div class="top-left">
                    {{ $cpny_id }} - {{ $cpny_name }}<br>
                    {{ $title }}<br>
                    ({{ $doc_type }})
                </div>
            </td>

            <td style="width: 45%; text-align:center;">
                <div class="title">{{ $cpny_name ?: $cpny_id }}</div>
            </td>

            <td style="width: 25%;">
                <div class="doc-no">No. {{ $docid }}</div>
            </td>
        </tr>
    </table>

    {{-- BODY INFO --}}
    <table class="info-wrapper">
        <tr>
            <td class="left-info">
                <table class="info-left-table">
                    <tr>
                        <td class="info-label">RFCA No</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $calr->rfcaid ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Requester</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $created_by_name ?: $created_by_username }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Department</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $department_id ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Dibayarkan Kpd/Vendor</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $vendorname ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="info-label">Keperluan/Purpose</td>
                        <td class="info-colon">:</td>
                        <td class="info-value">{{ $keperluan ?: '-' }}</td>
                    </tr>
                </table>
            </td>

            <td class="right-info">
                <table class="info-right-table">
                    <tr>
                        <td class="right-label">Tgl/Date</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">{{ $calrdate ?: '-' }}</td>
                    </tr>

                    <tr>
                        <td class="right-label">RFCA Amount</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">Rp. {{ number_format($amountRfca, 0, ',', '.') }}</td>
                    </tr>

                    <tr>
                        <td class="right-label">Calr Amount</td>
                        <td class="right-colon">:</td>
                        <td class="right-value nowrap">Rp. {{ number_format($amountCalr, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- AMOUNT SUMMARY --}}
    <table class="amount-summary">
        <tr>
            <td style="width: 25%;">Amount RFCA</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">Rp. {{ number_format($amountRfca, 0, ',', '.') }}</td>

            <td style="width: 25%;">Amount Calr</td>
            <td style="width: 2%;">:</td>
            <td style="width: 23%;">Rp. {{ number_format($amountCalr, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td>Lebih/Kurang</td>
            <td>:</td>
            <td colspan="4">Rp. {{ number_format($amountBalance, 0, ',', '.') }}</td>
        </tr>
    </table>

    {{-- PREV RFCA --}}
    @if (!empty($rfca->prev_rfcaid))
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 34%;">Prev RFCA ID</th>
                    <th style="width: 33%;">Prev RFCA Amount</th>
                    <th style="width: 33%;">Add RFCA Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">{{ $rfca->prev_rfcaid }}</td>
                    <td class="text-right">Rp. {{ number_format($rfca->prev_rfca_amount ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp. {{ number_format($rfca->add_rfca_amount ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    {{-- EXPENSE / PO DETAIL --}}
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
                    <span class="{{ $statusColor }}">{{ $status_doc }}</span>
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
