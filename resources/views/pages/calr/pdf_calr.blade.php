<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} ({{ $doc_type }})</title>

    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 18mm 15mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Header */
        .left-header th:first-child {
            width: 70%;
            text-align: left;
        }

        .left-header th:last-child {
            width: 30%;
            text-align: right;
        }

        .right-header th:first-child {
            width: 70%;
            text-align: left;
        }

        .right-header th:last-child {
            width: 30%;
            text-align: right;
        }

        .label {
            font-weight: bold;
        }

        /* Body fields */
        .left-body th {
            text-align: left;
            vertical-align: top;
            padding: 4px 0;
            font-weight: normal;
        }

        .field-row {
            display: flex;
            gap: 6px;
        }

        .field-label {
            min-width: 120px;
            /* font-weight: bold; */
        }

        .field-value-wrap {
            flex: 1;
            white-space: normal;
            word-wrap: break-word;
        }

        /* Expense table */
        .exp-table {
            width: 100%;
            border: 1px solid #000;
            margin-top: 15px;
        }

        .exp-table th {
            border: 1px solid #000;
            padding: 6px;
            background: #f7f7f7;
            font-weight: bold;
            text-align: left;
        }

        .exp-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        /* Approval Table */
        .sig-table {
            width: 100%;
            border: 1px solid #000;
            margin-top: 15px;
        }

        .sig-table th,
        .sig-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            font-size: 11px;
            word-wrap: break-word;
        }

        .sig-name {
            font-weight: bold;
        }

        .sig-status {
            margin-top: 2px;
            font-size: 11px;
        }

        .sig-num {
            font-weight: bold;
            margin-right: 4px;
        }

        .status {
            font-weight: bold;
        }

        .status.blue {
            color: blue;
        }

        .status.red {
            color: red;
        }

        .status.orange {
            color: orange;
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <table style="margin-bottom:10px;">
        <tr class="left-header">
            <th>{{ $cpny_id }} - {{ $cpny_name }}</th>
            <th><span class="label">No</span> : {{ $docid }}</th>
        </tr>
        <tr class="right-header">
            <th style="padding-top:6px;">{{ $title }} ({{ $doc_type }})</th>
            <th style="padding-top:6px;"><span class="label">Date</span> : {{ $calrdate }}</th>
        </tr>
    </table>

    <hr style="border:0; border-top:2px solid #000; margin-bottom:12px;">


    <!-- BODY FIELDS -->
    <table>
        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Dibayarkan Kpd :</span>
                    <span class="field-value-wrap">{{ $vendorname }}</span>
                </div>
            </th>
            {{-- <th>
                <div class="field-row">
                    <span class="field-label">Lokasi :</span>
                    <span class="field-value-wrap"></span>
                </div>
            </th> --}}
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Keperluan :</span>
                    <span class="field-value-wrap">
                       {{ $keperluan }}
                    </span>
                </div>
            </th>
            <th></th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Total Amount :</span>
                    <span class="field-value-wrap">{{ number_format($rfca_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Total Expenses :</span>
                    <span class="field-value-wrap">{{ number_format($calr_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </th>
        </tr>


        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Lebih/Kurang :</span>
                    <span class="field-value-wrap">{{ number_format($balance_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </th>
            <th></th>
        </tr>
    </table>


    <!-- EXPENSE TABLE -->
   <table class="exp-table">
        <thead>
            <tr>
                <th style="width:70%;">Description</th>
                <th style="width:30%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $dt)
                <tr>
                    <td>{{ $dt->inventory_descr }}</td>
                    <td>{{ number_format($dt->totalcost, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>


    <!-- CREATED BY FOOTER IS HANDLED INSIDE APPROVALS -->
    {{-- Approvals --}}
    @php
        $stColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            $status_doc === 'Hold' => 'orange',
            default => 'black',
        };

        // Creator becomes approval #1
        $prepared = collect([
            (object) [
                'aprv_name' => $created_by_name ?? $created_by_username,
                'status' => 'Created',
                'aprv_dateafter' => $req_date_fmt,
                'is_creator' => true,
            ],
        ])->merge($approval);

        $colsPerRow = 5;
        $chunks = $prepared->values()->chunk($colsPerRow);
        $idx = 1;
    @endphp

    <table class="sig-table">
        <thead>
            <tr>
                <th colspan="{{ $colsPerRow }}">
                    Status:
                    <span class="status {{ $stColor }}">{{ $status_doc }}</span>
                </th>
            </tr>
        </thead>

        <tbody>
            @foreach ($chunks as $chunk)
                <tr>
                    @foreach ($chunk as $dt2)
                        @php
                            if (isset($dt2->is_creator)) {
                                $label = 'Created';
                                $color = 'blue';
                                $dateStr = $dt2->aprv_dateafter;
                            } else {
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
                            }
                        @endphp

                        <td style="width:20%;">
                            <div><span class="sig-num">{{ $idx++ }}.</span>
                                <span class="sig-name">{{ $dt2->aprv_name }}</span>
                            </div>
                            <div class="sig-status {{ $color }}">{{ $label }}</div>
                            <div>{{ $dateStr }}</div>
                        </td>
                    @endforeach

                    {{-- Fill empty cells --}}
                    @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                        <td style="width:20%;">&nbsp;</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
