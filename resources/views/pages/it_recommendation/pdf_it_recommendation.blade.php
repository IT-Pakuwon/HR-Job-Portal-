<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>IT Recommendation</title>

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
           ITEMS TABLE
        ========================= */

        .items-table {
            margin-top: 10px;
            margin-bottom: 22px;
        }

        .items-table th {
            border: 1px solid #d9d9d9;
            background: #f7f7f7;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }

        .items-table td {
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

        @php
            $statusLabels = [
                'W' => 'Waiting IT',
                'I' => 'Waiting IT Revision',
                'P' => 'Waiting Approval',
                'C' => 'Completed',
                'R' => 'Rejected',
                'D' => 'Revise',
                'X' => 'Cancelled',
            ];
        @endphp

        {{-- HEADER --}}
        <table class="header">

            <tr>

                <td>

                    <div class="title">
                        IT RECOMMENDATION
                    </div>

                    <div class="company">
                        {{ $header->cpny_id ?? '-' }}
                    </div>

                </td>

                <td style="text-align:right;">

                    <div class="doc-number">
                        {{ $header->docid }}
                    </div>

                    <div class="doc-date">
                        {{ $header->itrecommend_date ? \Carbon\Carbon::parse($header->itrecommend_date)->format('d F Y') : '-' }}
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
                    <td class="meta-value">{{ $header->user_peminta ?? '-' }}</td>

                    <td class="meta-label">Department</td>
                    <td class="meta-value">{{ $header->department_id ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Company</td>
                    <td class="meta-value">{{ $header->cpny_id ?? '-' }}</td>

                    <td class="meta-label">Requested By</td>
                    <td class="meta-value">{{ $header->created_by ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Date</td>
                    <td class="meta-value">
                        {{ $header->itrecommend_date ? \Carbon\Carbon::parse($header->itrecommend_date)->format('d-m-Y') : '-' }}
                    </td>

                    <td class="meta-label">Status</td>
                    <td class="meta-value">{{ $statusLabels[$header->status] ?? $header->status }}</td>
                </tr>

                <tr>
                    <td class="meta-label">IT PIC</td>
                    <td class="meta-value">{{ $header->recommend_pic ?? '-' }}</td>

                    <td class="meta-label">Ticket Number</td>
                    <td class="meta-value">{{ $header->ticketnbr ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Asset Number</td>
                    <td class="meta-value" colspan="3">{{ $header->assetnbr ?? '-' }}</td>
                </tr>

                <tr>
                    <td class="meta-label">Purpose / Requirement</td>
                    <td class="meta-value" colspan="3" style="white-space: pre-wrap;">{{ $header->keperluan ?? '-' }}</td>
                </tr>

            </tbody>

        </table>

        {{-- IT RECOMMENDATION --}}
        <div class="section-title">
            IT Recommendation
        </div>

        <table class="meta-table">

            <tbody>

                <tr>
                    <td class="meta-label">Recommendation Type</td>
                    <td class="meta-value">{{ $header->recommend_type ?? '-' }}</td>

                    <td class="meta-label">Warranty</td>
                    <td class="meta-value">{{ $header->waranty ?? '-' }}</td>
                </tr>

                @if ($header->recommendation)
                    <tr>
                        <td class="meta-label">Notes</td>
                        <td class="meta-value" colspan="3" style="white-space: pre-wrap;">{{ $header->recommendation }}</td>
                    </tr>
                @endif

            </tbody>

        </table>

        {{-- RECOMMENDATION ITEMS --}}
        @if ($details->count())

            <div class="section-title">
                Recommendation Items
            </div>

            <table class="items-table">

                <thead>
                    <tr>
                        <th style="width: 30px;">No</th>
                        <th>Description</th>
                        <th style="width: 50px;">Qty</th>
                        <th style="width: 60px;">UOM</th>
                        <th style="width: 90px;">Category</th>
                        <th>Note</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($details as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row->recommend_descr ?? '-' }}</td>
                            <td>{{ $row->qty ?? '-' }}</td>
                            <td>{{ $row->uom ?? '-' }}</td>
                            <td>{{ $row->category ?? '-' }}</td>
                            <td>{{ $row->recommend_note ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>

            </table>

        @endif

        {{-- ATTACHMENTS --}}
        @if (!empty($attachments))

            @php
                $imageExts = ['jpg', 'jpeg', 'png'];
                $imgAtts   = array_values(array_filter($attachments, fn($a) => in_array(strtolower($a['extention'] ?? ''), $imageExts)));
                $fileAtts  = array_values(array_filter($attachments, fn($a) => !in_array(strtolower($a['extention'] ?? ''), $imageExts)));
            @endphp

            <div class="section-title">
                Attachments ({{ count($attachments) }})
            </div>

            @if (!empty($imgAtts))
                @php $imgChunks = array_chunk($imgAtts, 3); @endphp

                <table style="margin-bottom: 10px; border-collapse: collapse; table-layout: fixed;">

                    @foreach ($imgChunks as $imgRow)
                        <tr>

                            @foreach ($imgRow as $img)
                                <td style="width: 33%; border: 1px solid #d9d9d9; padding: 4px; vertical-align: top;">

                                    @if (!empty($img['base64']))
                                        <img src="{{ $img['base64'] }}"
                                            style="width: 100%; height: 120px; display: block;">
                                    @endif

                                    <div style="font-size: 9px; color: #555; margin-top: 3px; word-break: break-all;">
                                        {{ $img['display_name'] ?? $img['filename'] }}
                                    </div>

                                </td>
                            @endforeach

                            @for ($i = count($imgRow); $i < 3; $i++)
                                <td style="width: 33%;"></td>
                            @endfor

                        </tr>
                    @endforeach

                </table>

            @endif

            @if (!empty($fileAtts))

                <table class="items-table" style="margin-bottom: 22px;">

                    <thead>
                        <tr>
                            <th style="width: 60px;">Type</th>
                            <th>File Name</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($fileAtts as $file)
                            <tr>
                                <td style="text-transform: uppercase; font-weight: bold;">
                                    {{ $file['extention'] ?? '-' }}
                                </td>
                                <td>{{ $file['display_name'] ?? $file['filename'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>

            @endif

        @endif

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

                    <th style="width: 180px;">
                        Request By
                    </th>

                    @for ($i = 1; $i <= $colsPerRow; $i++)
                        <th>Approval</th>
                    @endfor

                </tr>

            </thead>

            <tbody>

                @forelse ($chunks as $rowIndex => $chunk)

                    <tr>

                        {{-- REQUESTER --}}
                        @if ($rowIndex === 0)
                            <td rowspan="{{ $chunks->count() }}">

                                <div class="approval-name">
                                    {{ strtoupper($header->user_peminta ?? $header->created_by ?? '-') }}
                                </div>

                                <div class="approval-status status-approved">
                                    Created
                                </div>

                                <div class="approval-date">
                                    {{ optional($header->created_at)->format('d M Y H:i') }}
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
                                    {{ strtoupper($aprv->aprv_name ?? $aprv->aprv_username ?? '-') }}
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
                                {{ strtoupper($header->user_peminta ?? $header->created_by ?? '-') }}
                            </div>

                            <div class="approval-status status-approved">
                                Created
                            </div>

                            <div class="approval-date">
                                {{ optional($header->created_at)->format('d M Y H:i') }}
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
