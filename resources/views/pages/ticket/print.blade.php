<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $ticket->ticketid }} — IT Support Ticket</title>

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

        .doc-status {
            margin-top: 4px;
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            color: #444;
        }

        .divider {
            border-top: 1px solid #d0d5dd;
            margin-bottom: 20px;
        }

        /* =========================
           SECTION WRAPPER
        ========================= */

        .section {
            margin-bottom: 18px;
            border: 1px solid #d9dde6;
            overflow: hidden;
        }

        .section-header {
            background: #f0f2f5;
            color: #2c3e50;
            font-size: 10.5px;
            font-weight: bold;
            padding: 5px 10px;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-bottom: 1px solid #d9dde6;
            border-left: 3px solid #555e6e;
        }

        .section-body {
            padding: 0;
        }

        /* =========================
           META TABLE
        ========================= */

        .meta-table {
            table-layout: fixed;
            width: 100%;
        }

        .meta-table td {
            border-top: 1px solid #e8ecf2;
            border-right: 1px solid #e8ecf2;
            padding: 7px 10px;
            vertical-align: top;
            font-size: 11px;
            word-wrap: break-word;
        }

        .meta-table tr:first-child td {
            border-top: none;
        }

        .meta-table td:last-child {
            border-right: none;
        }

        .meta-label {
            width: 130px;
            background: #f4f6fb;
            font-weight: bold;
            color: #333;
        }

        .meta-value {
            color: #222;
        }

        .meta-value a,
        .meta-label a {
            color: inherit;
            text-decoration: none;
        }

        /* =========================
           DESCRIPTION BOX
        ========================= */

        .desc-box {
            padding: 10px 12px;
            background: #fafcff;
            font-size: 11px;
            color: #334155;
            line-height: 1.6;
            word-wrap: break-word;
            border-top: 1px solid #e8ecf2;
        }

        .desc-label {
            font-size: 10px;
            font-weight: bold;
            color: #888;
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 5px;
        }

        /* Quill content in DomPDF */
        .ql-editor { padding: 0 !important; min-height: unset !important; }
        .ql-container.ql-snow, .ql-toolbar { border: none !important; }
        .ql-editor p { margin: 0 0 4px 0; }
        .ql-editor strong, .ql-editor b { font-weight: bold; }
        .ql-editor em, .ql-editor i { font-style: italic; }
        .ql-editor ul { margin: 0; padding-left: 16px; list-style-type: disc; }
        .ql-editor ol { margin: 0; padding-left: 16px; list-style-type: decimal; }
        .ql-editor li { margin-bottom: 2px; }
        .ql-editor img { max-width: 100%; height: auto; display: block; margin: 4px 0; }
        .ql-editor a { color: inherit; text-decoration: none; }

        /* =========================
           SIGNATURE TABLE
        ========================= */

        .approval-table {
            table-layout: fixed;
            width: 100%;
        }

        .approval-table th {
            border-top: none;
            border-right: 1px solid #e8ecf2;
            border-bottom: 1px solid #e8ecf2;
            background: #f4f6fb;
            padding: 7px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }

        .approval-table th:last-child {
            border-right: none;
        }

        .approval-table td {
            border-right: 1px solid #e8ecf2;
            border-top: 1px solid #e8ecf2;
            padding: 12px 10px;
            vertical-align: top;
            height: 70px;
        }

        .approval-table td:last-child {
            border-right: none;
        }

        .approval-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 14px;
            color: #111;
        }

        .approval-role {
            font-size: 10px;
            color: #666;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer-note {
            margin-top: 16px;
            font-size: 10px;
            color: #777;
            font-style: italic;
        }
    </style>
</head>
<body>

    @php
        $statusLabel = match($ticket->status) {
            'P' => 'Open',
            'C' => 'Completed',
            'X' => 'Cancelled',
            default => $ticket->status,
        };

        $pName  = optional($ticket->priority)->ticket_priority_name ?? $ticket->ticket_priority ?? '-';
        $pLower = strtolower($pName);
    @endphp

    <div class="page">

        {{-- HEADER --}}
        <table class="header">
            <tr>
                <td>
                    <div class="title">IT SUPPORT TICKET</div>
                    <div class="company">{{ $ticket->cpny_id ?? '-' }} &nbsp;·&nbsp; {{ $ticket->department_id ?? '-' }}</div>
                </td>
                <td style="text-align:right;">
                    <div class="doc-number">{{ $ticket->ticketid }}</div>
                    <div class="doc-date">{{ optional($ticket->ticketdate)->format('d F Y') ?? '-' }}</div>
                    <div class="doc-status">{{ $statusLabel }}</div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- SECTION: TICKET INFORMATION --}}
        <div class="section">
            <div class="section-header">Ticket Information</div>
            <div class="section-body">
                <table class="meta-table">
                    <tbody>
                        <tr>
                            <td class="meta-label">Requester</td>
                            <td class="meta-value">{{ $ticket->user_peminta ?? $ticket->created_by ?? '-' }}</td>
                            <td class="meta-label">Company</td>
                            <td class="meta-value">{{ $ticket->cpny_id ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Department</td>
                            <td class="meta-value">{{ $ticket->department_id ?? '-' }}</td>
                            <td class="meta-label">Status</td>
                            <td class="meta-value">{{ $statusLabel }} / {{ $ticket->status_pekerjaan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Ticket Type</td>
                            <td class="meta-value">{{ optional($ticket->type)->ticket_type_name ?? $ticket->ticket_type ?? '-' }}</td>
                            <td class="meta-label">Priority</td>
                            <td class="meta-value">{{ $pName }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Category</td>
                            <td class="meta-value">{{ optional($ticket->category)->ticket_category_name ?? '-' }}</td>
                            <td class="meta-label">Sub Category</td>
                            <td class="meta-value">{{ optional($ticket->subcategory)->ticket_subcategory_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Location</td>
                            <td class="meta-value">{{ optional($ticket->location)->location_name ?? '-' }}</td>
                            <td class="meta-label">Sub Location</td>
                            <td class="meta-value">{{ optional($ticket->subLocation)->sub_location_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">SLA Due Date</td>
                            <td class="meta-value">{{ optional($ticket->ticket_duedate)->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="meta-label">Submitted Date</td>
                            <td class="meta-value">{{ optional($ticket->ticketdate)->format('d M Y') ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECTION: ISSUE --}}
        <div class="section">
            <div class="section-header">Issue</div>
            <div class="section-body">
                <table class="meta-table">
                    <tbody>
                        <tr>
                            <td class="meta-label" style="width:130px;">Issue Summary</td>
                            <td class="meta-value" style="font-weight:bold;">{{ $ticket->issue_summary ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                @if ($ticket->issue_descr)
                    <div class="desc-box">
                        <div class="desc-label">Issue Description</div>
                        <div class="ql-editor">{!! $ticket->issue_descr !!}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION: SOLUTION --}}
        <div class="section">
            <div class="section-header">Solution</div>
            <div class="section-body">
                <table class="meta-table">
                    <tbody>
                        <tr>
                            <td class="meta-label">Responded By</td>
                            <td class="meta-value">{{ $respondedBy ?? '-' }}</td>
                            <td class="meta-label">Completed By</td>
                            <td class="meta-value">{{ $ticket->completed_by ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Completed At</td>
                            <td class="meta-value">{{ optional($ticket->completed_at)->format('d M Y, H:i') ?? '-' }}</td>
                            <td class="meta-label">IT PIC</td>
                            <td class="meta-value">{{ $ticket->pic_ticket ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
                @if ($ticket->solution_descr)
                    <div class="desc-box">
                        <div class="desc-label">Solution / Resolution Description</div>
                        <div class="ql-editor">{!! $ticket->solution_descr !!}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION: ATTACHMENTS --}}
        @if (!empty($attachments))
            @php
                $imageExts = ['jpg', 'jpeg', 'png'];
                $imgAtts   = array_values(array_filter($attachments, fn($a) => in_array(strtolower($a['extention'] ?? ''), $imageExts)));
                $fileAtts  = array_values(array_filter($attachments, fn($a) => !in_array(strtolower($a['extention'] ?? ''), $imageExts)));
            @endphp

            <div class="section">
                <div class="section-header">Attachments ({{ count($attachments) }})</div>
                <div class="section-body" style="padding: 10px 12px;">

                    @if (!empty($imgAtts))
                        @php $imgChunks = array_chunk($imgAtts, 3); @endphp
                        <table style="margin-bottom:10px; border-collapse:collapse; table-layout:fixed; width:100%;">
                            @foreach ($imgChunks as $imgRow)
                                <tr>
                                    @foreach ($imgRow as $img)
                                        <td style="width:33%; border:1px solid #e8ecf2; padding:4px; vertical-align:top;">
                                            @if (!empty($img['base64']))
                                                <img src="{{ $img['base64'] }}" style="width:100%; height:120px; display:block;">
                                            @endif
                                            <div style="font-size:9px; color:#555; margin-top:3px; word-break:break-all;">
                                                {{ $img['display_name'] ?? $img['filename'] }}
                                            </div>
                                        </td>
                                    @endforeach
                                    @for ($i = count($imgRow); $i < 3; $i++)
                                        <td style="width:33%;"></td>
                                    @endfor
                                </tr>
                            @endforeach
                        </table>
                    @endif

                    @if (!empty($fileAtts))
                        <table class="meta-table">
                            <thead>
                                <tr>
                                    <th style="width:60px; background:#f4f6fb; border:1px solid #e8ecf2; padding:6px 10px; text-align:left; font-size:11px; font-weight:bold; color:#333;">Type</th>
                                    <th style="background:#f4f6fb; border:1px solid #e8ecf2; border-left:none; padding:6px 10px; text-align:left; font-size:11px; font-weight:bold; color:#333;">File Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fileAtts as $file)
                                    <tr>
                                        <td class="meta-label" style="text-transform:uppercase;">{{ $file['extention'] ?? '-' }}</td>
                                        <td class="meta-value">{{ $file['display_name'] ?? $file['filename'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                </div>
            </div>
        @endif

        {{-- SECTION: ACKNOWLEDGEMENT (completed tickets only) --}}
        @if ($ticket->status === 'C')
            <div class="section">
                <div class="section-header">Acknowledgement</div>
                <div class="section-body">
                    <table class="approval-table">
                        <thead>
                            <tr>
                                <th>Requester</th>
                                <th>IT Technician / PIC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="approval-name">{{ strtoupper($ticket->user_peminta ?? $ticket->created_by ?? '-') }}</div>
                                    <div class="approval-role">Ticket Requester</div>
                                </td>
                                <td>
                                    <div class="approval-name">{{ strtoupper($ticket->completed_by ?? $ticket->pic_ticket ?? '-') }}</div>
                                    <div class="approval-role">IT Support / PIC</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="footer-note">
            * Generated by Pakuwon APP System &nbsp;·&nbsp; {{ now()->format('d M Y, H:i') }}
        </div>

    </div>

</body>
</html>
