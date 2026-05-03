<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>Meeting Room Report</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
        }

        .header {
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 11px;
            color: #6b7280;
        }

        .meta {
            margin-top: 10px;
            font-size: 10px;
            color: #4b5563;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        tbody td {
            border: 1px solid #e5e7eb;
            padding: 7px;
            vertical-align: top;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge-active {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-size: 10px;
            font-weight: bold;
        }

        .badge-cancel {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 10px;
            font-weight: bold;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
            text-align: right;
        }
    </style>
</head>

<body>

    <div class="header">

        <div class="title">
            Meeting Room Report
        </div>

        <div class="subtitle">
            General Affairs Reporting
        </div>

        <div class="meta">

            Generated:
            {{ now()->format('d M Y H:i') }}

            <br>

            @if(request('date_from') || request('date_to'))

                Period:
                {{ request('date_from') ?: '-' }}
                →
                {{ request('date_to') ?: '-' }}

            @endif

        </div>

    </div>

    <table>

    <thead>
        <tr>

            <th>Doc ID</th>

            <th>Date</th>

            <th>Start</th>

            <th>End</th>

            <th>Room</th>

            <th>Accessories</th>

            <th>Title</th>

            <th>Requester</th>

            <th>Department</th>

            <th>Participants</th>

            <th>Type</th>

            <th>External Company</th>

            <th>Duration</th>

            <th>Status</th>

        </tr>
    </thead>

        <tbody>

            @forelse($data as $i => $row)

            <tr>

                <td>{{ $row->docid }}</td>

                <td>
                    {{ \Carbon\Carbon::parse($row->meeting_date)->format('d-M-Y') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($row->start_meeting_time)->format('H:i') }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($row->end_meeting_time)->format('H:i') }}
                </td>

                <td>{{ $row->room_name }}</td>

                <td>{{ $row->accessories ?: '-' }}</td>

                <td>{{ $row->meeting_title }}</td>

                <td>{{ $row->user_peminta }}</td>

                <td>{{ $row->department_name ?: '-' }}</td>

                <td>{{ $row->total_participant }}</td>

                <td>
                    {{ $row->external_participant ? 'External' : 'Internal' }}
                </td>

                <td>
                    {{ $row->external_company ?: '-' }}
                </td>

                <td>

                    {{
                        round(
                            \Carbon\Carbon::parse($row->start_meeting_time)
                                ->diffInMinutes(
                                    \Carbon\Carbon::parse($row->end_meeting_time)
                                ) / 60,
                            1
                        )
                    }} hrs

                </td>

                <td>

                    @if($row->status === 'X')

                        Cancelled

                    @else

                        Active

                    @endif

                </td>

            </tr>

            @empty

                <tr>

                    <td colspan="8" class="text-center">
                        No data available
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">

        Total Records:
        {{ count($data) }}

    </div>

</body>

</html>
