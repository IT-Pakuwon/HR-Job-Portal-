<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Voucher Taxi</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .page {
            padding: 28px 34px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* =========================
           HEADER
        ========================= */

        .header {
            margin-bottom: 18px;
        }

        .header td {
            vertical-align: top;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: .8px;
            color: #111;
        }

        .company {
            margin-top: 4px;
            font-size: 12px;
            color: #555;
        }

        .doc-number {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        .doc-date {
            margin-top: 4px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }

        .divider {
            margin-top: 14px;
            border-top: 1px solid #dcdcdc;
        }

        /* =========================
           META TABLE
        ========================= */

        .meta-table {
            margin-top: 18px;
            margin-bottom: 22px;
            table-layout: fixed;
        }

        .meta-table td {
            border: 1px solid #d9d9d9;
            padding: 8px 10px;
            vertical-align: top;
            font-size: 11px;
            word-wrap: break-word;
        }

        .meta-label {
            width: 140px;
            background: #f7f7f7;
            font-weight: bold;
            color: #333;
        }

        .meta-value {
            color: #222;
        }

        /* =========================
           SECTION TITLE
        ========================= */

        .section-title {
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #222;
        }

        /* =========================
           APPROVAL TABLE
        ========================= */

        .approval-table {
            margin-top: 8px;
            table-layout: fixed;
        }

        .approval-table th {
            border: 1px solid #d9d9d9;
            background: #f7f7f7;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }

        .approval-table td {
            border: 1px solid #d9d9d9;
            padding: 10px;
            vertical-align: top;
            height: 105px;
        }

        .approval-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 18px;
            color: #111;
        }

        .approval-status {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 18px;
        }

        .status-approved {
            color: #1565c0;
        }

        .status-waiting {
            color: #ef6c00;
        }

        .status-rejected {
            color: #c62828;
        }

        .approval-date {
            font-size: 10px;
            color: #666;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer-note {
            margin-top: 14px;
            font-size: 10px;
            color: #777;
            font-style: italic;
        }
    </style>
</head>

<body>

    <div class="page">

        {{-- HEADER --}}
        <table class="header">

            <tr>

                <td>

                    <div class="title">
                        VOUCHER TAXI
                    </div>

                    <div class="company">
                        {{ $company->cpny_name ?? '-' }}
                    </div>

                </td>

                <td style="text-align:right;">

                    <div class="doc-number">
                        {{ $voucher->docid }}
                    </div>

                    <div class="doc-date">
                        {{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d F Y') }}
                    </div>

                </td>

            </tr>

        </table>

        <div class="divider"></div>

        {{-- META --}}
        <table class="meta-table">

            <tbody>

                <tr>
                    <td class="meta-label">Requester</td>
                    <td class="meta-value">
                        {{ $voucher->user_peminta }}
                    </td>

                    <td class="meta-label">Department</td>
                    <td class="meta-value">
                        {{ $voucher->department_id }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Origin</td>
                    <td class="meta-value">
                        {{ $voucher->origin }}
                    </td>

                    <td class="meta-label">Destination</td>
                    <td class="meta-value">
                        {{ $voucher->destination }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Date Used</td>
                    <td class="meta-value">
                        {{ \Carbon\Carbon::parse($voucher->date_used)->format('d-m-Y') }}
                    </td>

                    <td class="meta-label">Type Trip</td>
                    <td class="meta-value">
                        {{ $voucher->type_trip }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Purpose</td>
                    <td class="meta-value" colspan="3">
                        {{ $voucher->purpose }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Topup</td>
                    <td class="meta-value">
                        {{ $voucher->user_topup }}
                    </td>

                    <td class="meta-label">Company Expense</td>
                    <td class="meta-value">
                        {{ $voucher->cpny_id_expense }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Actual Expense</td>
                    <td class="meta-value" colspan="3">
                        Rp {{ number_format($voucher->actual_budget ?? 0, 0, ',', '.') }}
                    </td>
                </tr>

            </tbody>

        </table>

        {{-- APPROVAL --}}
        <div class="section-title">
            Approval Information
        </div>

        @php

            $colsPerRow = $approvals->count() > 5 ? 4 : 3;

            $chunks = $approvals->values()->chunk($colsPerRow);

            $totalCols = 1 + $colsPerRow;

        @endphp

        <table class="approval-table">

            <thead>

                <tr>

                    <th style="width:180px;">
                        Request By
                    </th>

                    @for($i = 1; $i <= $colsPerRow; $i++)

                        <th>
                            Approval
                        </th>

                    @endfor

                </tr>

            </thead>

            <tbody>

                @forelse($chunks as $rowIndex => $chunk)

                    <tr>

                        {{-- REQUESTER --}}
                        @if($rowIndex === 0)

                            <td rowspan="{{ $chunks->count() }}">

                                <div class="approval-name">
                                    {{ strtoupper($voucher->user_peminta) }}
                                </div>

                                <div class="approval-status status-approved">
                                    Created
                                </div>

                                <div class="approval-date">
                                    {{ optional($voucher->created_at)->format('d M Y H:i') }}
                                </div>

                            </td>

                        @endif

                        {{-- APPROVALS --}}
                        @foreach($chunk as $aprv)

                            @php

                                $label = match ($aprv->status) {
                                    'A' => 'Approved',
                                    'R' => 'Rejected',
                                    'P' => 'Waiting',
                                    default => 'Revised',
                                };

                                $statusClass = match ($aprv->status) {
                                    'A' => 'status-approved',
                                    'R' => 'status-rejected',
                                    'P' => 'status-waiting',
                                    default => 'status-rejected',
                                };

                            @endphp

                            <td>

                                <div class="approval-name">
                                    {{ strtoupper($aprv->aprv_name ?? '-') }}
                                </div>

                                <div class="approval-status {{ $statusClass }}">
                                    {{ $label }}
                                </div>

                                <div class="approval-date">

                                    @if($aprv->aprv_dateafter)

                                        {{ \Carbon\Carbon::parse($aprv->aprv_dateafter)->format('d M Y H:i') }}

                                    @else

                                        -

                                    @endif

                                </div>

                            </td>

                        @endforeach

                        {{-- EMPTY --}}
                        @for($i = $chunk->count(); $i < $colsPerRow; $i++)

                            <td>&nbsp;</td>

                        @endfor

                    </tr>

                @empty

                    <tr>

                        <td>

                            <div class="approval-name">
                                {{ strtoupper($voucher->user_peminta) }}
                            </div>

                            <div class="approval-status status-approved">
                                Created
                            </div>

                            <div class="approval-date">
                                {{ optional($voucher->created_at)->format('d M Y H:i') }}
                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        <div class="footer-note">
            * Approval generated by Pakuwon APP System
        </div>

    </div>

</body>

</html>
