<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Booking Car</title>

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
           ROUTE TABLE
        ========================= */

        .route-table {
            margin-top: 10px;
            margin-bottom: 22px;
        }

        .route-table th {
            border: 1px solid #d9d9d9;
            background: #f7f7f7;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }

        .route-table td {
            border: 1px solid #d9d9d9;
            padding: 8px;
            vertical-align: top;
            font-size: 11px;
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
                        BOOKING CAR
                    </div>

                    <div class="company">
                        {{ $company->cpny_name ?? '-' }}
                    </div>

                </td>

                <td style="text-align:right;">

                    <div class="doc-number">
                        {{ $booking->docid }}
                    </div>

                    <div class="doc-date">
                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}
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
                        {{ $booking->user_peminta }}
                    </td>

                    <td class="meta-label">Department</td>
                    <td class="meta-value">
                        {{ $booking->department_id }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Booking Date</td>
                    <td class="meta-value">
                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d-m-Y') }}
                    </td>

                    <td class="meta-label">Company Site</td>
                    <td class="meta-value">
                        {{ $booking->cpny_id_site }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Start Time</td>
                    <td class="meta-value">
                        {{ \Carbon\Carbon::parse($booking->start_time)->format('d M Y H:i') }}
                    </td>

                    <td class="meta-label">End Time</td>
                    <td class="meta-value">
                        {{ \Carbon\Carbon::parse($booking->end_time)->format('d M Y H:i') }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Purpose</td>
                    <td class="meta-value" colspan="3">

                        @if ($booking->purpose_id === 'OTHER')

                            {{ $booking->purpose_descr }}
                        @else
                            {{ $booking->purpose_id }}

                            @if ($booking->purpose_descr)
                                - {{ $booking->purpose_descr }}
                            @endif

                        @endif

                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Passenger</td>
                    <td class="meta-value">
                        {{ $booking->passenger ?? '-' }}
                    </td>

                    <td class="meta-label">Requested By</td>
                    <td class="meta-value">
                        {{ $booking->created_by }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Driver</td>
                    <td class="meta-value">
                        {{ $booking->driver ?? '-' }}
                    </td>

                    <td class="meta-label">Vehicle</td>
                    <td class="meta-value">
                        {{ $booking->no_polisi ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">Handphone</td>
                    <td class="meta-value">
                        {{ $booking->handphone ?? '-' }}
                    </td>

                    <td class="meta-label">Status</td>
                    <td class="meta-value">
                        {{ $status_doc }}
                    </td>
                </tr>

                <tr>
                    <td class="meta-label">User Request</td>
                    <td class="meta-value" colspan="3">
                        {{ $booking->user_request ?? '-' }}
                    </td>
                </tr>

            </tbody>

        </table>

        {{-- ROUTE --}}
        <div class="section-title">
            Route Information
        </div>

        <table class="route-table">

            <thead>

                <tr>
                    <th style="width:50px;">No</th>
                    <th>Location From</th>
                    <th>Destination</th>
                </tr>

            </thead>

            @php

                $routesFrom = is_array($booking->location_from)
                    ? $booking->location_from
                    : json_decode($booking->location_from ?: '[]', true);

                $routesTo = is_array($booking->destination)
                    ? $booking->destination
                    : json_decode($booking->destination ?: '[]', true);

                $routesFrom = is_array($routesFrom) ? $routesFrom : [];
                $routesTo = is_array($routesTo) ? $routesTo : [];

            @endphp

            <tbody>

                @forelse($routesFrom as $index => $from)

                    <tr>

                        <td>
                            {{ $index + 1 }}
                        </td>

                        <td>
                            {{ $from }}
                        </td>

                        <td>
                            {{ $routesTo[$index] ?? '-' }}
                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="3" style="text-align:center;">
                            No route information
                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- APPROVAL --}}
        <div class="section-title">
            Approval Information
        </div>

        @php

            $colsPerRow = $approvals->count() > 5 ? 4 : 3;

            $chunks = $approvals->values()->chunk($colsPerRow);

        @endphp

        <table class="approval-table">

            <thead>

                <tr>

                    <th style="width:180px;">
                        Request By
                    </th>

                    @for ($i = 1; $i <= $colsPerRow; $i++)
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
                        @if ($rowIndex === 0)
                            <td rowspan="{{ $chunks->count() }}">

                                <div class="approval-name">
                                    {{ strtoupper($booking->user_peminta) }}
                                </div>

                                <div class="approval-status status-approved">
                                    Created
                                </div>

                                <div class="approval-date">
                                    {{ optional($booking->created_at)->format('d M Y H:i') }}
                                </div>

                            </td>
                        @endif

                        {{-- APPROVALS --}}
                        @foreach ($chunk as $aprv)
                            @php

                                $label = match ($aprv->status) {
                                    'A' => 'Approved',
                                    'R' => 'Rejected',
                                    'P' => 'Waiting',
                                    'D' => 'Revised',
                                    default => '-',
                                };

                                $statusClass = match ($aprv->status) {
                                    'A' => 'status-approved',
                                    'R' => 'status-rejected',
                                    'P' => 'status-waiting',
                                    'D' => 'status-rejected',
                                    default => 'status-waiting',
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

                                    @if ($aprv->aprv_dateafter)
                                        {{ \Carbon\Carbon::parse($aprv->aprv_dateafter)->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif

                                </div>

                            </td>
                        @endforeach

                        {{-- EMPTY --}}
                        @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                            <td>&nbsp;</td>
                        @endfor

                    </tr>

                @empty

                    <tr>

                        <td>

                            <div class="approval-name">
                                {{ strtoupper($booking->user_peminta) }}
                            </div>

                            <div class="approval-status status-approved">
                                Created
                            </div>

                            <div class="approval-date">
                                {{ optional($booking->created_at)->format('d M Y H:i') }}
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
