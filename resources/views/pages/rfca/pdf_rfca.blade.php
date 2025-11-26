<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Berita Acara Serah Terima (BAST)</title>

    <style>
        @page {
            size: 8.5in 5.5in;
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        /* First column 70%, second column 30% */
        .left-header th:first-child,
        .right-header th:first-child {
            width: 70%;
        }

        .left-header th:last-child,
        .right-header th:last-child {
            width: 30%;
            text-align: right;
        }

        .left-body th {
            text-align: left;
            vertical-align: top;
            font-weight: normal;
            padding: 2px 0;
        }

        .left-body th:first-child {
            width: 50%;
        }

        .left-body th:last-child {
            width: 50%;
        }

        /* Hanya label bold */
        .label {
            font-weight: bold;
        }

        /* Alignment label : value */
        .field-label {
            display: inline-block;
            min-width: 100px; /* atur sesuai kebutuhan */
        }

        .field-colon {
            display: inline-block;
            width: 10px;
            text-align: center;
        }

        .field-value {
            display: inline-block;
        }

        /* Signature / approval table */
        .sig-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .sig-table th,
        .sig-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 11px;
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
            margin: 2px 0;
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
    {{-- Header --}}
    <table>
        <tr class="left-header">
            <th style="text-align: left">
                {{ $cpnyname }}
            </th>
            <th style="text-align:left;">
                <span style="font-weight:bold;">No :</span>
                <span style="font-weight:normal;">{{ $docid }}</span>
            </th>
        </tr>
        <tr class="right-header">
            <th style="text-align: left">{{ $title }}</th>
            <th style="text-align:left;">
                <span style="font-weight:bold;">Date :</span>
                <span style="font-weight:normal;">{{ $rfcadate }}</span>
            </th>
        </tr>
    </table>

    <hr>

    {{-- Body --}}
    <table>
        <tr class="left-body">
            <th>
                <span class="field-label">No SPPJ</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $rfca->sppbjktid }}</span>
            </th>
            <th>
                <span class="field-label">Garansi</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $spkwarranty }}</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">No CS</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $rfca->csid }}</span>
            </th>
            <th>
                <span class="field-label">Keterangan</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $rfca->keperluan }}</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">PO Nbr</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $rfca->ponbr }}</span>
            </th>
            <th>
                <span class="field-label">Penalty/hari</span>
                <span class="field-colon">:</span>
                <span class="field-value">
                    {{ number_format($penalty_per_day ?? 0, 0, ',', '.') }}
                </span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">Vendor Name</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $rfca->vendorname }}</span>
            </th>
            <th>
                <span class="field-label">Total Penalty</span>
                <span class="field-colon">:</span>
                <span class="field-value">
                    {{ number_format($total_penalty ?? 0, 0, ',', '.') }}
                </span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">Status</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $status_doc }}</span>
            </th>
            <th>
                <span class="field-label">Lokasi</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $location_name }}</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">Start Date</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $startdate_fmt }}</span>
            </th>
            <th>
                <span class="field-label">Sub Lokasi</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $sub_location_name }}</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">End Date</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $enddate_fmt }}</span>
            </th>
            <th>
                <span class="field-label">BAST Amount</span>
                <span class="field-colon">:</span>
                <span class="field-value">
                    {{ number_format($rfca_amount ?? 0, 0, ',', '.') }}
                </span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label">HO date</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $handoverdate_fmt }}</span>
            </th>
            <th>
                <span class="field-label">Realisasi</span>
                <span class="field-colon">:</span>
                <span class="field-value">
                    {{ number_format($realize_amount ?? 0, 0, ',', '.') }}
                </span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="field-label label">Created by</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $created_by_name ?? $created_by_username }}</span>
            </th>
            <th>
                <span class="field-label label">On</span>
                <span class="field-colon">:</span>
                <span class="field-value">{{ $req_date_fmt }}</span>
            </th>
        </tr>
    </table>

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
