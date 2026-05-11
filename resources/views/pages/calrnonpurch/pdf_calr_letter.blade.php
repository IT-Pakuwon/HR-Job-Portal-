<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Advance Liquidation Report (CALR)</title>

    <style>
        @page {
            size: 8.5in 5.5in;
            /* HALF LETTER */
            margin: 10mm 10mm 12mm 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            /* smaller for half letter */
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* HEADER */
        .left-header th:first-child {
            width: 65%;
            text-align: left;
        }

        .left-header th:last-child {
            width: 35%;
            text-align: right;
        }

        .right-header th:first-child {
            width: 65%;
            text-align: left;
        }

        .right-header th:last-child {
            width: 35%;
            text-align: right;
        }

        .label {
            font-weight: bold;
        }

        /* BODY FIELDS */
        .left-body th {
            text-align: left;
            vertical-align: top;
            padding: 2px 0;
            font-weight: normal;
        }

        .field-row {
            display: flex;
            gap: 4px;
        }

        .field-label {
            min-width: 100px;
            /* smaller for half-letter */
            /* font-weight: bold; */
        }

        .field-value-wrap {
            flex: 1;
            white-space: normal;
            word-wrap: break-word;
        }

        /* EXPENSE TABLE */
        .exp-table {
            width: 100%;
            border: 1px solid #000;
            margin-top: 10px;
        }

        .exp-table th {
            border: 1px solid #000;
            padding: 5px;
            background: #f7f7f7;
            font-weight: bold;
            text-align: left;
            font-size: 10.5px;
        }

        .exp-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10.5px;
        }

        /* APPROVAL TABLE */
        .sig-table {
            width: 100%;
            border: 1px solid #000;
            margin-top: 12px;
        }

        .sig-table th,
        .sig-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
            text-align: left;
            word-wrap: break-word;
        }

        .sig-name {
            font-weight: bold;
        }

        .sig-status {
            margin-top: 2px;
            font-size: 10px;
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
    <table style="margin-bottom:6px;">
        <tr class="left-header">
            <th>AW - Artisan Wahyu, PT</th>
            <th><span class="label">No</span> : CR25110015</th>
        </tr>

        <tr class="right-header">
            <th style="padding-top:4px;">Cash Advance Liquidation Report (CALR)</th>
            <th style="padding-top:4px;"><span class="label">Date</span> : 11/21/2025</th>
        </tr>
    </table>

    <hr style="border:0; border-top:1.5px solid #000; margin-bottom:10px;">


    <!-- BODY FIELDS -->
    <table>
        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Dibayarkan Kpd :</span>
                    <span class="field-value-wrap">IBU CAS</span>
                </div>
            </th>

            <th>
                <div class="field-row">
                    <span class="field-label">Lokasi :</span>
                    <span class="field-value-wrap"></span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Keperluan :</span>
                    <span class="field-value-wrap">
                        Pembelian Stocking untuk Staff Wanita Periode
                        Oktober - Desember 2025 (Okt, Nov, Des)
                    </span>
                </div>
            </th>
            <th></th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Total Amount :</span>
                    <span class="field-value-wrap">1,200,000.00</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Total Expenses :</span>
                    <span class="field-value-wrap">1,200,000.00</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Lebih/Kurang :</span>
                    <span class="field-value-wrap">0.00</span>
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
            <tr>
                <td>STOCKING</td>
                <td>1,200,000.00</td>
            </tr>
        </tbody>
    </table>


    {{-- APPROVALS --}}
    @php
        $stColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            $status_doc === 'Hold' => 'orange',
            default => 'black',
        };

        // Creator becomes #1 approver
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

                    {{-- Empty cells --}}
                    @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                        <td style="width:20%;">&nbsp;</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
