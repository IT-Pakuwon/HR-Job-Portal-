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
            /* optional */
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

        /* Make only the label bold */
        .label {
            font-weight: bold;
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
                <span style="font-weight:normal;">{{ $bastdate }}</span>
            </th>
        </tr>
    </table>

    <hr>

    {{-- Body --}}
    <table>
        <tr class="left-body">
            <th>
                <span class="#">No SPPJ :</span>
                <span>PJ25100024</span>
            </th>
            <th>
                <span class="#">Garansi :</span>
                <span></span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">No CS :</span>
                <span>CS25100100</span>
            </th>
            <th>
                <span class="#">Keterangan :</span>
                <span>Pengangkutan Limbah B3 Gandaria City Superblok tahun 2025</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">Po Nbr :</span>
                <span>8000006261</span>
            </th>
            <th>
                <span class="#">Penalty/hari :</span>
                <span>250,000.00</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">Vendor Name :</span>
                <span>PRASADHA PAMUNAH LIMBAH INDUSTRI, PT</span>
            </th>
            <th>
                <span class="#">Total Penalty :</span>
                <span>0.00</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">Status :</span>
                <span>Completed</span>
            </th>
            <th>
                <span class="#">Lokasi :</span>
                <span>LANTAI B2</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">Start Date :</span>
                <span>9/27/2025</span>
            </th>
            <th>
                <span class="#">Lokasi :</span>
                <span>LANTAI B2</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">End Date :</span>
                <span>12/12/2025</span>
            </th>
            <th>
                <span class="#">Sub Lokasi :</span>
                <span>RUANG LIMBAH B3</span>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="#">HO date :</span>
                <span>11/3/2025 10:12 AM</span>
            </th>
            <th></th>
        </tr>

        <tr class="left-body">
            <th>
                <span class="label">Created by :</span>
                <span>Andre Febriadi</span>
            </th>
            <th>
                <span class="label">On :</span>
                <span>10/31/2025 6:48 PM</span>
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
