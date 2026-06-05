<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Ticket Created
    </title>

</head>

<body
    style="
    margin:0;
    padding:32px 16px;
    background:#f3f4f6;
    font-family:
        Inter,
        Arial,
        sans-serif;
    color:#111827;
">

    <div style="
        max-width:720px;
        margin:0 auto;
    ">

        <div
            style="
            overflow:hidden;
            border-radius:24px;
            background:#ffffff;
            border:1px solid #e5e7eb;
            box-shadow:
                0 10px 30px rgba(0,0,0,0.06);
        ">

            <div
                style="
                padding:32px 36px;
                background:
                    linear-gradient(
                        135deg,
                        #111827 0%,
                        #1f2937 100%
                    );
            ">

                <div
                    style="
                    display:inline-flex;
                    align-items:center;
                    justify-content:center;
                    width:52px;
                    height:52px;
                    border-radius:16px;
                    background:
                        rgba(255,255,255,0.12);
                    font-size:24px;
                    margin-bottom:20px;
                ">
                    🎫
                </div>

                <h1
                    style="
                    margin:0;
                    font-size:28px;
                    font-weight:700;
                    color:#ffffff;
                    letter-spacing:-0.03em;
                ">
                    New Ticket Created
                </h1>

                <p
                    style="
                    margin:12px 0 0;
                    font-size:14px;
                    line-height:24px;
                    color:#d1d5db;
                ">
                    A new support ticket has been submitted
                    into the IT Ticketing System.
                </p>

            </div>

            <div style="
                padding:36px;
            ">

                <table width="100%" cellpadding="0" cellspacing="0"
                    style="
                        border-collapse:collapse;
                    ">

                    <tr>
                        <td
                            style="
                            width:180px;
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Ticket ID
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            font-weight:700;
                            color:#111827;
                        ">
                            {{ $ticket->ticketid }}
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Requester
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#111827;
                        ">
                            {{ $ticket->user_peminta }}
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Ticket Type
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#111827;
                        ">
                            {{ $ticket->ticket_type }}
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Category
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#111827;
                        ">
                            {{ $ticket->category->ticket_category_name ?? $ticket->ticket_categoryid }}
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
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

                            <span
                                style="
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
                        <td
                            style="
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Summary
                        </td>

                        <td
                            style="
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
                        <td
                            style="
                            padding:14px 0;
                            font-size:13px;
                            font-weight:600;
                            color:#6b7280;
                            vertical-align:top;
                        ">
                            Description
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#374151;
                            line-height:26px;
                        ">
                            {!! nl2br(e($ticket->issue_descr)) !!}
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="
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

                            <span
                                style="
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

            </div>

            <div
                style="
                padding:24px 36px;
                background:#f9fafb;
                border-top:1px solid #e5e7eb;
            ">

                <div
                    style="
                    font-size:12px;
                    line-height:22px;
                    color:#6b7280;
                ">

                    This is an automated email from
                    IT Ticketing System.

                    <br><br>

                    Please do not reply directly to this
                    email.

                </div>

            </div>

        </div>

    </div>

</body>

</html>
