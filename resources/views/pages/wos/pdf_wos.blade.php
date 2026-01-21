<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            vertical-align: top;
            padding: 3px 4px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .border-top {
            border-top: 1px solid #000;
        }

        .border-bottom {
            border-bottom: 1px solid #000;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
        }

        .subtitle {
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }

        .section-title {
            font-weight: bold;
        }

        .spacer {
            height: 10px;
        }

        /* === Signature / approval table (with border) === */
        .sig-table {
            width: 100%;
            border-collapse: separate;
            /* ⬅️ penting */
            border-spacing: 0;
            /* ⬅️ rapat seperti collapse */
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
    <!-- Header -->
    <table>
        <tr>
            <td style="width: 33%;"><strong> {{ $cpnyid }} - {{ $cpnyname }}</strong></td>
            <td style="width: 34%; text-align: center; vertical-align: middle;">
                <div class="title">{{ $title }}</div>
                <div class="subtitle">{{ $wotype }}</div>
            </td>
            <td style="width: 33%;">
                <table>
                    <tr>
                        <td>WO ID</td>
                        <td>:</td>
                        <td>{{ $docid }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>:</td>
                        <td>{{ $wodate }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>:</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Status WO</td>
                        <td>:</td>
                        <td>{{ $status_doc }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="spacer"></div>
    <div class="border-top"></div>

    <!-- Info Section -->
    <table style="margin-top: 8px; width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 12%;">User</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $created_by_name }}</td>

            <td style="width: 12%;">Company</td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $cpnyid }}</td>

            <td style="width: 12%;">Department</td>
            <td style="width: 2%;">:</td>
            <td style="width: 18%;">{{ $department_id }}</td>
        </tr>

        <tr>
            <td>Type</td>
            <td>:</td>
            <td>{{ $worequest }}</td>

            <td>Request</td>
            <td>:</td>
            <td>{{ $cpnyid }} - {{ $cpnyname }}</td>

            <td>PIC</td>
            <td>:</td>
            <td>{{ $picrequester }}</td>
        </tr>

        <tr>
            <td>Jenis Pekerjaan</td>
            <td>:</td>
            <td>{{ $worktype_name }}</td>

            <td>Sub Jenis Pekerjaan</td>
            <td>:</td>
            <td>{{ $subworktype_name }}</td>

            <td>Biaya WO</td>
            <td>:</td>
            <td>Rp. {{ $biaya_wo }}</td>
        </tr>

        <tr>
            <td>Lokasi</td>
            <td>:</td>
            <td>{{ $location_name }}</td>

            <td>Sub Lokasi</td>
            <td>:</td>
            <td>{{ $sub_location_name }}</td>

            <td colspan="3"></td>
        </tr>
    </table>


    <div class="border-bottom" style="margin-top: 8px;"></div>

    <!-- Job Details -->
    <table style="margin-top: 10px;">
        <tr>
            <td style="width: 17%;">Info Pekerjaan</td>
            <td style="width: 2%;">:</td>
            <td>{{ $keperluan }}</td>
        </tr>
    </table>

    <div class="spacer"></div>
    <div class="border-top"></div>

    <!-- Estimation & Realization -->
    {{-- <table style="margin-top: 8px;">
        <tr>
            <td style="width: 45%;"><strong>Estimasi Pekerjaan Work Order</strong></td>
            <td>:</td>
            <td>1/1/1901</td>
            <td style="width: 2%;" class="text-center">s/d</td>
            <td>1/1/1901</td>
        </tr>
        <tr>
            <td><strong>Realisasi Pekerjaan Work Order</strong></td>
            <td>:</td>
            <td>1/1/1901</td>
            <td class="text-center">s/d</td>
            <td>1/1/1901</td>
        </tr>
    </table> --}}

    <div class="border-bottom" style="margin-top: 8px;"></div>
    {{-- Approvals --}}
    @php
        $approvalCount = $approval->count();
    @endphp

    @php
        $colsPerRow = $approvalCount > 5 ? 4 : 3;
        $chunks = $approval->values()->chunk($colsPerRow);
        $idx = 1;
        $totalCols = 1 + $colsPerRow;
    @endphp

    <table class="sig-table">
        <thead>
            <tr>
                <th colspan="{{ $totalCols }}" style="text-align:left;">
                    Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($chunks as $rowIndex => $chunk)
                <tr>
                    @if ($rowIndex === 0)
                        <td rowspan="{{ $chunks->count() }}" style="width:160px; border-right:1px solid #000;">
                            <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
                            <div class="sig-status blue">Created</div>
                            <div>{{ $req_date_fmt }}</div>
                        </td>
                    @endif

                    @foreach ($chunk as $dt2)
                        <td>
                            <div><span class="sig-num">{{ $idx++ }}.</span>
                                <span class="sig-name">{{ $dt2->name }}</span>
                            </div>
                            <div class="sig-status {{ $dt2->status === 'A' ? 'blue' : 'orange' }}">
                                {{ $dt2->status === 'A' ? 'Approved' : 'Waiting' }}
                            </div>
                            <div>
                                {{ $dt2->aprvdateafter ? \Carbon\Carbon::parse($dt2->aprvdateafter)->format('d M Y H:i') : '' }}
                            </div>
                        </td>
                    @endforeach

                    @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                        <td>&nbsp;</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

</body>

</html>
