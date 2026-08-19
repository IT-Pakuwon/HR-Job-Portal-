<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $rfp->rfp_id }}</title>

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
            width: 25%;
            text-align: right;
        }

        .detail-budget {
            width: 40%;
            font-size: 11px;
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

        /* ============ FINANCE FLOW ============ */
        .finance-title {
            margin-top: 20px;
            margin-bottom: 5px;
            font-size: 15px;
            font-weight: bold;
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

        $typePo = strtoupper(trim((string) ($rfp->type_po ?? '')));
        $isKontrak = !empty($isKontrak);
        $kontrakBudgets = $kontrakBudgets ?? collect();

        $irNote = trim((string) ($rfp->ir_note ?? ''));
        $purpose = mb_strlen($irNote) >= 5 ? $irNote : ($rfp->keperluan ?? '');

        $statusColor = match ($rfp->status) {
            'C' => 'blue',
            'R', 'X' => 'red',
            'D', 'P' => 'orange',
            default => 'black',
        };

        $approvalRows = collect($approval ?? [])->values();
        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = $approvalRows->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    {{-- HEADER --}}
    <table class="header-table">
        <tr>
            <td style="width: 24%;">
                <div class="left-company">
                    {{ $cpny_name ?: $rfp->cpny_id }}
                </div>

                <div class="left-title">
                    REQUEST FOR PAYMENT<br>
                    (RFP PURCHASE)
                </div>
            </td>

            <td style="width: 54%; text-align: center;">
                <div class="header-title">
                    {{ $cpny_name ?: $rfp->cpny_id }}
                </div>
            </td>

            <td style="width: 22%;">
                <div class="doc-no">
                    No. {{ $rfp->rfp_id }}
                </div>
            </td>
        </tr>
    </table>

    <hr class="header-rule">

    {{-- BODY INFO --}}
    <table class="info-table">
        <tr>
            <td style="width: 31%;" class="label">Dibayarkan kpd/Vendor</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 30%;">{{ $rfp->vendor_name ?: '-' }}</td>

            <td style="width: 15%;" class="label">Tgl/Date</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 20%;" class="nowrap">{{ $fmtDate($rfp->rfp_date, 'd M Y') }}</td>
        </tr>

        <tr>
            <td class="label">PO No</td>
            <td class="colon">:</td>
            <td>{{ $rfp->ponbr ?: '-' }}</td>

            <td class="label">Contract/Kontrak</td>
            <td class="colon">:</td>
            <td class="nowrap">{{ $rfp->kontrak_id ?: '-' }}</td>
        </tr>

        <tr>
            <td class="label">Department</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfp->department_id ?: '-' }}</td>
        </tr>

        <tr>
            <td class="label">Jumlah/Amount</td>
            <td class="colon">:</td>
            <td colspan="4" class="nowrap">Rp {{ number_format((float) $rfp->rfp_amount, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td class="label">Terbilang/Amount in Words</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $rfp->terbilang ?: '-' }}</td>
        </tr>

        <tr>
            <td class="label">Keperluan/Purpose</td>
            <td class="colon">:</td>
            <td colspan="4">{{ $purpose ?: '-' }}</td>
        </tr>

        <tr>
            <td style="width: 32%;" class="label">
                Type PO
            </td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 66%;" colspan="4" class="nowrap">
                {{ $rfp->type_po ?: '-' }}
            </td>
        </tr>

        <tr>
            <td style="width: 32%;" class="label">
                Bentuk Pembayaran/Form Of Payment
            </td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 66%;" colspan="4" class="nowrap">
                {{ $rfp->payment_type ?: '-' }}
            </td>
        </tr>
    </table>

    {{-- DETAIL KONTRAK BUDGET --}}
    @if ($isKontrak)
        <div class="detail-title">
            Detail Kontrak Budget
        </div>

        <table class="detail-table">
            <colgroup>
                <col style="width: 6%;">
                <col style="width: 40%;">
                <col style="width: 29%;">
                <col style="width: 25%;">
            </colgroup>

            <thead>
                <tr>
                    <th class="detail-no">No</th>
                    <th>Budget</th>
                    <th class="detail-budget">Activity</th>
                    <th class="detail-amount">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($kontrakBudgets as $i => $b)
                    <tr>
                        <td class="detail-no">{{ $i + 1 }}</td>
                        <td>
                            <span class="b-line">
                                {{ $b->budget_business_unit_id ?: '-' }} /
                                {{ $b->budget_department_fin_id ?: '-' }} /
                                {{ $b->budget_account_id ?: '-' }}
                            </span>
                        </td>
                        <td class="detail-budget">
                            <span class="b-line b-desc">
                                {{ $b->budget_activity_descr ?: $b->budget_activity_id ?: '-' }}
                            </span>
                            <span class="b-line">
                                Available: Rp {{ number_format((float) ($b->budget_remaining ?? 0), 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="detail-amount">
                            Rp {{ number_format((float) ($b->rfp_base_amount ?? 0), 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center;">Detail kontrak budget belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">Total</td>
                    <td class="detail-amount">Rp {{ number_format((float) $kontrakBudgets->sum('rfp_base_amount'), 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- FINANCE FLOW --}}
    <div class="finance-title">
        Finance &amp; Treasury
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 20%;" class="label">Finance</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 30%;" class="{{ $rfp->status_receive === 'C' ? 'blue' : 'orange' }}">
                {{ $rfp->status_receive === 'C' ? 'Received' : 'Pending' }}
            </td>

            <td style="width: 20%;" class="label">Treasury</td>
            <td style="width: 2%;" class="colon">:</td>
            <td style="width: 30%;" class="{{ $rfp->status_payment === 'C' ? 'blue' : 'orange' }}">
                {{ $rfp->status_payment === 'C' ? 'Received' : 'Pending' }}
            </td>
        </tr>

        <tr>
            <td class="label">User Finance</td>
            <td class="colon">:</td>
            <td>{{ $rfp->user_receive ?: '-' }}</td>

            <td class="label">User Treasury</td>
            <td class="colon">:</td>
            <td>{{ $rfp->user_payment ?: '-' }}</td>
        </tr>

        <tr>
            <td class="label">Date Finance</td>
            <td class="colon">:</td>
            <td class="nowrap">{{ $rfp->receive_date_fmt ?? $fmtDate($rfp->receive_date, 'd M Y H:i') }}</td>

            <td class="label">Date Treasury</td>
            <td class="colon">:</td>
            <td class="nowrap">{{ $rfp->payment_date_fmt ?? $fmtDate($rfp->payment_date, 'd M Y H:i') }}</td>
        </tr>
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
                            $aprvStatus = strtoupper(trim((string) $dt2->status));

                            $label = match ($aprvStatus) {
                                'A', 'C' => 'Approved',
                                'R' => 'Rejected',
                                'P' => 'Waiting',
                                'D' => 'Revised',
                                default => 'Waiting',
                            };

                            $color = match ($aprvStatus) {
                                'A', 'C' => 'blue',
                                'R' => 'red',
                                'P' => 'orange',
                                'D' => 'red',
                                default => 'orange',
                            };

                            $dateValue = $dt2->aprv_dateafter ?: $dt2->aprv_datebefore;

                            $dateStr = $dateValue
                                ? \Carbon\Carbon::parse($dateValue)->format('d M Y H:i')
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
