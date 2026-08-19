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
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* ============ HEADER ============ */
        .header-table {
            table-layout: fixed;
        }

        .header-table td {
            padding: 0 3px;
        }

        .header-rule {
            border: none;
            border-top: 1px solid #000;
            margin: 6px 0 10px 0;
        }

        .header-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            line-height: 1.15;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .doc-no {
            font-size: 15px;
            font-weight: bold;
            white-space: nowrap;
            line-height: 1.15;
            text-align: right;
        }

        .left-company {
            font-size: 15px;
            font-weight: bold;
            line-height: 1.15;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .left-title {
            margin-top: 2px;
            font-size: 15px;
            font-weight: bold;
            line-height: 1.15;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* ============ INFO / FORM ============ */
        .info-table {
            table-layout: fixed;
        }

        .info-table td {
            font-size: 15px;
            line-height: 1.3;
            padding: 3px 3px;
            border-bottom: 1px solid #e0e0e0;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .info-table .label {
            white-space: normal;
        }

        .info-table .colon {
            text-align: center;
            white-space: nowrap;
        }

        .nowrap {
            white-space: nowrap;
        }

        /* ============ DETAIL SECTION ============ */
        .detail-title {
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 15px;
            font-weight: bold;
        }

        .detail-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 13px;
            table-layout: fixed;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        .detail-table th {
            background-color: #f2f2f2;
            text-align: left;
            font-weight: bold;
        }

        .detail-no {
            width: 6%;
            text-align: center;
        }

        .detail-amount {
            width: 18%;
            text-align: right;
        }

        .detail-tax {
            width: 14%;
            line-height: 1.35;
            color: #333;
        }

        .detail-budget {
            width: 27%;
            line-height: 1.35;
            color: #333;
        }

        .detail-budget .b-line {
            display: block;
        }

        .detail-budget .b-desc {
            font-weight: bold;
            color: #000;
        }

        .detail-table tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        /* ============ APPROVAL ============ */
        .approval-table {
            margin-top: 40px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .approval-table th,
        .approval-table td {
            border: 1px solid #000;
            padding: 7px;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
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
            color: #1a4d8f;
        }

        .red {
            color: #b02a2a;
        }

        .orange {
            color: #a15c00;
        }

        .black {
            color: #000;
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
    <table class="header-table">
        <tr>
            <td style="width: 24%;">
                <div class="left-company">
                    {{ $cpny_name ?: $rfpnonpurch->cpny_id }}
                </div>

                <div class="left-title">
                    {{ $docTitle }}<br>
                    {{ $docSubtitle }}
                </div>
            </td>

            <td style="width: 54%; text-align: center;">
                <div class="header-title">
                    {{ $cpny_name ?: $rfpnonpurch->cpny_id }}
                </div>
            </td>

            <td style="width: 22%;">
                <div class="doc-no">
                    No. {{ $rfpnonpurch->rfpnonpurchaseid }}
                </div>
            </td>
        </tr>
    </table>

    <hr class="header-rule">

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
            <td colspan="4">{{ $rfpnonpurch->pleasepayto ?: '-' }}</td>
        </tr>

        <tr>
            <td class="label">Jumlah/Amount</td>
            <td class="colon">:</td>
            <td colspan="4" class="nowrap">Rp. {{ number_format($amount, 0, ',', '.') }}</td>
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
            <td class="label">Business Unit</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfpnonpurch->business_unit_name ?: ($rfpnonpurch->business_unit_id ?: '-') }}</td>
        </tr>

        <tr>
            <td class="label">Group Biaya</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfpnonpurch->groupbiaya->groupbiayadescr ?? '-' }}</td>
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

    {{-- DETAIL --}}
    @if (isset($details) && $details->count())
        @if (!$isRCA)
            <div class="detail-title">
                Detail RFP Non Purchase
            </div>
        @endif

        @php
            $noW = !$isRCA ? 6 : 0;
            $amtW = 18;
            $taxW = !empty($hasTaxDetail) ? 14 : 0;
            $budgetW = !empty($hasBudgetDetail) ? 27 : 0;
            $descW = 100 - $noW - $amtW - $taxW - $budgetW;
        @endphp

        <table class="detail-table">
            <colgroup>
                @if (!$isRCA)
                    <col style="width: {{ $noW }}%;">
                @endif
                <col style="width: {{ $descW }}%;">
                <col style="width: {{ $amtW }}%;">
                @if (!empty($hasTaxDetail))
                    <col style="width: {{ $taxW }}%;">
                @endif
                @if (!empty($hasBudgetDetail))
                    <col style="width: {{ $budgetW }}%;">
                @endif
            </colgroup>

            <thead>
                <tr>
                    @if (!$isRCA)
                        <th class="detail-no">No</th>
                    @endif
                    <th>{{ $isRCA ? 'Keperluan' : 'Description' }}</th>
                    <th class="detail-amount">Amount Request</th>
                    @if (!empty($hasTaxDetail))
                        <th class="detail-tax">Tax</th>
                    @endif
                    @if (!empty($hasBudgetDetail))
                        <th class="detail-budget">Budget</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach ($details as $i => $d)
                    <tr>
                        @if (!$isRCA)
                            <td class="detail-no">{{ $i + 1 }}</td>
                        @endif
                        <td>{{ $isRCA ? ($rfpnonpurch->keperluan ?: '-') : ($d->keperluan_detail ?: '-') }}</td>
                        <td class="detail-amount">Rp {{ number_format((float) ($d->amount_request ?? 0), 2, ',', '.') }}</td>

                        @if (!empty($hasTaxDetail))
                            <td class="detail-tax">
                                @php
                                    $taxData = $d->tax_data ?? null;
                                    $taxRate = (float) ($taxData->taxrate ?? 0);
                                @endphp

                                <span class="b-line b-desc">
                                    {{ optional($taxData)->descr ?: ($d->taxcodeid ?: '-') }}
                                    @if ($taxRate > 0)
                                        ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)
                                    @endif
                                </span>
                            </td>
                        @endif

                        @if (!empty($hasBudgetDetail))
                            <td class="detail-budget">
                                @php
                                    $budgetData = $d->budget_data ?? null;
                                @endphp

                                @if ($budgetData)
                                    <span class="b-line">
                                        {{ $d->budget_business_unit_id ?: '-' }} /
                                        {{ $d->budget_department_fin_id ?: '-' }} /
                                        {{ $d->budget_account_id ?: '-' }}
                                    </span>
                                    <span class="b-line b-desc">
                                        {{ $d->budget_activity_descr ?: $d->budget_activity_id ?: '-' }}
                                    </span>
                                    <span class="b-line">
                                        Available: Rp {{ number_format((float) ($d->budget_remaining ?? 0), 0, ',', '.') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="{{ $isRCA ? 1 : 2 }}" style="text-align: right;">Total</td>
                    <td class="detail-amount">Rp {{ number_format((float) $details->sum('amount_request'), 2, ',', '.') }}</td>
                    @if (!empty($hasTaxDetail))
                        <td></td>
                    @endif
                    @if (!empty($hasBudgetDetail))
                        <td></td>
                    @endif
                </tr>
            </tfoot>
        </table>
    @endif

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
