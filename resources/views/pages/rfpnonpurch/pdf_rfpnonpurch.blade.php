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

        .sig-table td, .sig-table th {
            border: 1px solid #000;
            padding: 6px;
        }

        .status.blue { color: blue; }
        .status.red { color: red; }
        .status.orange { color: orange; }
    </style>
</head>

<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td width="33%">
            <strong>{{ $rfpnonpurch->cpny_id }} - {{ $cpny_name }}</strong>
        </td>

        <td width="34%" style="text-align:center;">
            <div class="title">REQUEST FOR PAYMENT</div>
            <div class="subtitle">(RFP)</div>
        </td>

        <td width="33%">
            <table>
                <tr><td>No</td><td>:</td><td>{{ $rfpnonpurch->rfpnonpurchaseid }}</td></tr>
                <tr><td>Date</td><td>:</td><td>{{ \Carbon\Carbon::parse($rfpnonpurch->rfpnonpurchasedate)->format('d M Y') }}</td></tr>
                {{-- <tr><td>Status</td><td>:</td><td>{{ $rfpnonpurch->status }}</td></tr> --}}
            </table>
        </td>
    </tr>
</table>

<div class="border-top"></div>

{{-- ================= INFO ================= --}}
<table style="margin-top:8px;">
    <tr>
        <td width="15%">Vendor</td><td width="2%">:</td>
        <td>{{ $rfpnonpurch->vendor_name }}</td>

        <td width="15%">Department</td><td width="2%">:</td>
        <td>{{ $rfpnonpurch->department_id }}</td>
    </tr>

    <tr>
        <td>PO No</td><td>:</td>
        <td>{{ $rfpnonpurch->ponbr }}</td>

        <td>Contract</td><td>:</td>
        <td>{{ $rfpnonpurch->kontrak_id }}</td>
    </tr>
</table>

<div class="border-bottom" style="margin-top:8px;"></div>

{{-- ================= AMOUNT ================= --}}
<table style="margin-top:8px;">
    <tr>
        <td width="20%">Amount</td><td width="2%">:</td>
        <td>Rp {{ number_format($rfpnonpurch->amountrequestpayment) }}</td>
    </tr>

    <tr>
        <td>Terbilang</td><td>:</td>
        <td>{{ $rfpnonpurch->terbilang }}</td>
    </tr>

    <tr>
        <td>Purpose</td><td>:</td>
        <td>{{ $rfpnonpurch->keperluan }}</td>
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
        <td style="color:{{ $rfpnonpurch->status_receive === 'C' ? 'blue' : 'orange' }}">
            {{ $rfpnonpurch->status_receive === 'C' ? 'Received' : 'Pending' }}
        </td>

        <td width="20%">Treasury</td>
        <td width="2%">:</td>
        <td style="color:{{ $rfpnonpurch->status_payment === 'C' ? 'blue' : 'orange' }}">
            {{ $rfpnonpurch->status_payment === 'C' ? 'Received' : 'Pending' }}
        </td>
    </tr>

    <tr>
        <td>User Finance</td><td>:</td>
        <td>{{ $rfpnonpurch->user_receive ?? '-' }}</td>

        <td>User Treasury</td><td>:</td>
        <td>{{ $rfpnonpurch->user_payment ?? '-' }}</td>
    </tr>

    <tr>
        <td>Date Finance</td><td>:</td>
        <td>{{ $rfpnonpurch->receive_date ?? '-' }}</td>

        <td>Date Treasury</td><td>:</td>
        <td>{{ $rfpnonpurch->payment_date ?? '-' }}</td>
    </tr>
</table>

<div class="border-bottom" style="margin-top:8px;"></div>


    {{-- Approvals --}}
    @php
        $stColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            $status_doc === 'Hold' => 'orange',
            default => 'black',
        };

        $colsPerRow = $approve_count > 5 ? 4 : 3;
        $chunks = $approval->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    <table class="sig-table">
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}" style="text-align: left;">
                    Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($chunks as $rowIndex => $chunk)
                <tr>
                    @if ($rowIndex === 0)
                        <td rowspan="{{ $chunks->count() }}" style="width:160px;">
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
                                default => 'Revised',
                            };
                            $color = match ($dt2->status) {
                                'A' => 'blue',
                                'R' => 'red',
                                'P' => 'orange',
                                default => 'red',
                            };
                            $dateStr = $dt2->aprv_dateafter
                                ? \Carbon\Carbon::parse($dt2->aprv_dateafter)->format('d M Y H:i')
                                : '';
                        @endphp
                        <td>
                            <div><span class="sig-num">{{ $idx++ }}.</span><span
                                    class="sig-name">{{ $dt2->aprv_name }}</span></div>
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
