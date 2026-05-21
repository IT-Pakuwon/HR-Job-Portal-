<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>RFP</title>

    <style>
        @page { size: A4; margin: 12mm; }
        body { font-family: Arial; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 4px; }

        .title { font-size: 18px; font-weight: bold; text-align: center; }
        .subtitle { text-align: center; font-weight: bold; }

        .border-top { border-top: 1px solid #000; }
        .border-bottom { border-bottom: 1px solid #000; }

        /* .sig-table td, .sig-table th {
            border: 1px solid #000;
            padding: 6px;
        }

        .status.blue { color: blue; }
        .status.red { color: red; }
        .status.orange { color: orange; } */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .sig-table td,
        .sig-table th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .sig-table th {
            background: #f7f7f7;
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

        .status.blue,
        .blue {
            color: blue;
        }

        .status.red,
        .red {
            color: red;
        }

        .status.orange,
        .orange {
            color: orange;
        }

        .status.black,
        .black {
            color: black;
        }
    </style>
</head>

<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td width="33%">
            <strong>{{ $rfp->cpny_id }} - {{ $cpny_name }}</strong>
        </td>

        <td width="34%" style="text-align:center;">
            <div class="title">REQUEST FOR PAYMENT</div>
            <div class="subtitle">(RFP)</div>
        </td>

        <td width="33%">
            <table>
                <tr><td>No</td><td>:</td><td>{{ $rfp->rfp_id }}</td></tr>
                <tr><td>Date</td><td>:</td><td>{{ \Carbon\Carbon::parse($rfp->rfp_date)->format('d M Y') }}</td></tr>
                {{-- <tr><td>Status</td><td>:</td><td>{{ $rfp->status }}</td></tr> --}}
            </table>
        </td>
    </tr>
</table>

<div class="border-top"></div>

{{-- ================= INFO ================= --}}
<table style="margin-top:8px;">
    <tr>
        <td width="15%">Vendor</td><td width="2%">:</td>
        <td>{{ $rfp->vendor_name }}</td>

        <td width="15%">Department</td><td width="2%">:</td>
        <td>{{ $rfp->department_id }}</td>
    </tr>

    <tr>
        <td>PO No</td><td>:</td>
        <td>{{ $rfp->ponbr }}</td>

        <td>Contract</td><td>:</td>
        <td>{{ $rfp->kontrak_id }}</td>
    </tr>
</table>

<div class="border-bottom" style="margin-top:8px;"></div>

{{-- ================= AMOUNT ================= --}}
<table style="margin-top:8px;">
    <tr>
        <td width="20%">Amount</td><td width="2%">:</td>
        <td>Rp {{ number_format($rfp->rfp_amount) }}</td>
    </tr>

    <tr>
        <td>Terbilang</td><td>:</td>
        <td>{{ $rfp->terbilang }}</td>
    </tr>

    <tr>
        <td>Purpose</td><td>:</td>
        <td>{{ $rfp->keperluan }}</td>
    </tr>
</table>

<div class="border-bottom" style="margin-top:8px;"></div>

{{-- ================= 🔥 FINANCE FLOW ================= --}}
<table style="margin-top:10px;">
    <tr>
        <td colspan="6"><strong>Finance & Treasury</strong></td>
    </tr>

    <tr>
        <td width="20%">Finance</td>
        <td width="2%">:</td>
        <td style="color:{{ $rfp->status_receive === 'C' ? 'blue' : 'orange' }}">
            {{ $rfp->status_receive === 'C' ? 'Received' : 'Pending' }}
        </td>

        <td width="20%">Treasury</td>
        <td width="2%">:</td>
        <td style="color:{{ $rfp->status_payment === 'C' ? 'blue' : 'orange' }}">
            {{ $rfp->status_payment === 'C' ? 'Received' : 'Pending' }}
        </td>
    </tr>

    <tr>
        <td>User Finance</td><td>:</td>
        <td>{{ $rfp->user_receive ?? '-' }}</td>

        <td>User Treasury</td><td>:</td>
        <td>{{ $rfp->user_payment ?? '-' }}</td>
    </tr>

    <tr>
        <td>Date Finance</td><td>:</td>
        <td>{{ $rfp->receive_date ?? '-' }}</td>

        <td>Date Treasury</td><td>:</td>
        <td>{{ $rfp->payment_date ?? '-' }}</td>
    </tr>
</table>

<div class="border-bottom" style="margin-top:8px;"></div>


    {{-- ================= APPROVAL ================= --}}
    @php
        $approvalRows = collect($approval ?? [])->values();

        $stColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            in_array($status_doc, ['Revise', 'Revised', 'On Progress', 'Waiting Approval']) => 'orange',
            default => 'black',
        };

        $approve_count = $approvalRows->count();
        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = $approvalRows->chunk($colsPerRow);

        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    <table class="sig-table" style="margin-top:12px;">
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}" style="text-align: left;">
                    Status:
                    <span class="status {{ $stColor }}">
                        {{ $status_doc }}
                    </span>
                </th>
            </tr>
        </thead>

        <tbody>
            @if ($approvalRows->count() > 0)
                @foreach ($chunks as $rowIndex => $chunk)
                    <tr>
                        @if ($rowIndex === 0)
                            <td rowspan="{{ $chunks->count() }}" style="width:160px;">
                                <div class="sig-name">
                                    {{ $created_by_name ?? $created_by_username }}
                                </div>
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
                                    <span class="sig-num">{{ $idx++ }}.</span>
                                    <span class="sig-name">{{ $dt2->aprv_name }}</span>
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
                @endforeach
            @else
                <tr>
                    <td colspan="{{ $totalCols }}">
                        <div class="sig-name">
                            {{ $created_by_name ?? $created_by_username }}
                        </div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt }}</div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>


</body>
</html>
