<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

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

        .left-header th:first-child,
        .right-header th:first-child {
            width: 70%;
        }

        .left-header th:last-child,
        .right-header th:last-child {
            width: 30%;
            text-align: right;
        }

        .label {
            font-weight: bold;
        }

        .left-body th {
            text-align: left;
            vertical-align: top;
            padding: 4px 0;
            font-weight: normal;
        }

        .field-row {
            display: flex;
            gap: 4px;
        }

        .field-label {
            min-width: 110px;
        }

        .field-value-wrap {
            flex: 1;
            white-space: normal;
            word-wrap: break-word;
        }

        .sig-table {
            width: 100%;
            border: 1px solid #000;
            margin-top: 18px;
        }

        .sig-table th,
        .sig-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
            font-size: 11.5px;
            white-space: normal;
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

        .status.black {
            color: black;
        }

        .blue {
            color: blue;
        }

        .red {
            color: red;
        }

        .orange {
            color: orange;
        }

        .black {
            color: black;
        }
    </style>
</head>

<body>

    <table style="margin-bottom:8px;">
        <tr class="left-header">
            <th style="text-align:left;">
                {{ $cpny_id }} - {{ $cpny_name }}
            </th>
            <th style="text-align:right;">
                <span class="label">No</span> : {{ $docid }}
            </th>
        </tr>

        <tr class="right-header">
            <th style="text-align:left; padding-top:6px;">
                {{ $title }} ({{ $doc_type }})
            </th>
            <th style="text-align:right; padding-top:6px;">
                <span class="label">Date</span> : {{ $rfcadate }}
            </th>
        </tr>
    </table>

    <hr style="border:0; border-top:2px solid #000; margin:0 0 10px 0;">

    <table>
        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Kepada :</span>
                    <span class="field-value-wrap">TREASURY</span>
                </div>
            </th>

            <th>
                <div class="field-row">
                    <span class="field-label">Payment Ready Date :</span>
                    <span class="field-value-wrap">{{ $created_at_fmt }}</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Dibayarkan Kpd :</span>
                    <span class="field-value-wrap">{{ $vendorname }}</span>
                </div>
            </th>

            <th>
                <div class="field-row">
                    <span class="field-label">Payment Taken Date :</span>
                    <span class="field-value-wrap"></span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Jumlah / Amount :</span>
                    <span class="field-value-wrap">{{ number_format($rfca_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </th>

            <th>
                <div class="field-row">
                    <span class="field-label">Tgl CALR :</span>
                    <span class="field-value-wrap">{{ $calr_date }}</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Terbilang :</span>
                    <span class="field-value-wrap">{{ $terbilang }}</span>
                </div>
            </th>

            <th>
                <div class="field-row">
                    <span class="field-label">Tgl Diperlukan :</span>
                    <span class="field-value-wrap">{{ $required_date }}</span>
                </div>
            </th>
        </tr>

        <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Keperluan :</span>
                    <span class="field-value-wrap">{{ $keperluan }}</span>
                </div>
            </th>
        </tr>

        {{-- <tr class="left-body">
            <th>
                <div class="field-row">
                    <span class="field-label">Created by :</span>
                    <span class="field-value-wrap">{{ $created_by_name }}</span>
                </div>
            </th>
        </tr> --}}
    </table>

    @php
        $stColor = match (true) {
            in_array($status_doc, ['Approved', 'Completed']) => 'blue',
            in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
            $status_doc === 'Hold' => 'orange',
            default => 'black',
        };

        $prepared = collect([
            (object) [
                'rfca_step_user' => $created_by_name ?? $created_by_username,
                'status_rfca' => 'CREATED',
                'rfca_step_date' => $req_date_fmt,
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
                <th colspan="{{ $colsPerRow }}" style="text-align:left;">
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
                            if (!empty($dt2->is_creator)) {
                                $label = 'Created';
                                $color = 'blue';
                                $dateStr = $dt2->rfca_step_date;
                                $nameStr = $dt2->rfca_step_user;
                            } else {
                                $statusRaw = strtoupper((string) ($dt2->status_rfca ?? ''));

                                $label = match ($statusRaw) {
                                    'A', 'APPROVED' => 'Approved',
                                    'R', 'REJECTED' => 'Rejected',
                                    'P', 'WAITING' => 'Waiting',
                                    'D', 'REVISED', 'REVISE' => 'Revised',
                                    'C', 'COMPLETED' => 'Completed',
                                    default => $statusRaw !== '' ? ucfirst(strtolower($statusRaw)) : 'Waiting',
                                };

                                $color = match ($statusRaw) {
                                    'A', 'APPROVED', 'C', 'COMPLETED' => 'blue',
                                    'R', 'REJECTED' => 'red',
                                    'P', 'WAITING' => 'orange',
                                    'D', 'REVISED', 'REVISE' => 'red',
                                    default => 'black',
                                };

                                $dateStr = !empty($dt2->rfca_step_date)
                                    ? \Carbon\Carbon::parse($dt2->rfca_step_date)->format('d M Y H:i')
                                    : '';

                                $nameStr = $dt2->rfca_step_user ?: ($dt2->rfca_step_descr ?? '-');
                            }
                        @endphp

                        <td style="width:20%;">
                            <div>
                                <span class="sig-num">{{ $idx++ }}.</span>
                                <span class="sig-name">{{ $nameStr }}</span>
                            </div>
                            <div class="sig-status {{ $color }}">{{ $label }}</div>
                            <div>{{ $dateStr }}</div>
                        </td>
                    @endforeach

                    @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                        <td style="width:20%;">&nbsp;</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>