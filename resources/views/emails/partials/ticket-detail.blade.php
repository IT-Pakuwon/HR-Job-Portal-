<table width="100%"
    cellpadding="0"
    cellspacing="0"
    style="
        border-collapse:collapse;
    ">

    <tr>
        <td style="
            width:180px;
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Ticket ID
        </td>

        <td style="
            padding:14px 0;
            font-size:14px;
            font-weight:700;
            color:#111827;
        ">
            {{ $ticket->ticketid }}
        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Requester
        </td>

        <td style="
            padding:14px 0;
            font-size:14px;
            color:#111827;
        ">
            {{ $ticket->user_peminta }}
        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Ticket Type
        </td>

        <td style="
            padding:14px 0;
            font-size:14px;
            color:#111827;
        ">
            {{ $ticket->ticket_type }}
        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Priority
        </td>

        <td style="
            padding:14px 0;
        ">

            <span style="
                display:inline-block;
                padding:6px 12px;
                border-radius:999px;
                background:#fef3c7;
                color:#92400e;
                font-size:12px;
                font-weight:700;
            ">
                {{ $ticket->ticket_priority }}
            </span>

        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Summary
        </td>

        <td style="
            padding:14px 0;
            font-size:14px;
            color:#111827;
            line-height:24px;
            font-weight:600;
        ">
            {{ $ticket->issue_summary }}
        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Description
        </td>

        <td style="
            padding:14px 0;
            font-size:14px;
            color:#374151;
            line-height:26px;
        ">
            {!! nl2br(
                e($ticket->issue_descr)
            ) !!}
        </td>
    </tr>

    <tr>
        <td style="
            padding:14px 0;
            font-size:13px;
            font-weight:600;
            color:#6b7280;
            vertical-align:top;
        ">
            Status
        </td>

        <td style="
            padding:14px 0;
        ">

            <span style="
                display:inline-block;
                padding:6px 12px;
                border-radius:999px;
                background:#dbeafe;
                color:#1d4ed8;
                font-size:12px;
                font-weight:700;
            ">
                {{ $ticket->status }}
            </span>

        </td>
    </tr>

</table>
