<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        New Discussion Comment
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
                        #1e3a5f 0%,
                        #2563eb 100%
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
                    💬
                </div>

                <h1
                    style="
                    margin:0;
                    font-size:28px;
                    font-weight:700;
                    color:#ffffff;
                    letter-spacing:-0.03em;
                ">
                    New Discussion Comment
                </h1>

                <p
                    style="
                    margin:12px 0 0;
                    font-size:14px;
                    line-height:24px;
                    color:#bfdbfe;
                ">
                    A new comment has been posted on
                    <strong style="color:#ffffff;">{{ $moduleLabel }} — {{ $docNo }}</strong>
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
                            border-bottom:1px solid #f3f4f6;
                        ">
                            Module
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            font-weight:700;
                            color:#111827;
                            border-bottom:1px solid #f3f4f6;
                        ">
                            {{ $moduleLabel }}
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
                            border-bottom:1px solid #f3f4f6;
                        ">
                            Document No.
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            font-weight:700;
                            color:#2563eb;
                            border-bottom:1px solid #f3f4f6;
                        ">
                            {{ $docNo }}
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
                            border-bottom:1px solid #f3f4f6;
                        ">
                            Commented By
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#111827;
                            border-bottom:1px solid #f3f4f6;
                        ">
                            {{ $commenterName }}
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
                            Comment
                        </td>

                        <td
                            style="
                            padding:14px 0;
                            font-size:14px;
                            color:#374151;
                            line-height:26px;
                        ">

                            <div
                                style="
                                padding:16px 20px;
                                background:#f8fafc;
                                border-left:4px solid #2563eb;
                                border-radius:0 8px 8px 0;
                            ">
                                {!! nl2br(e($commentMessage)) !!}
                            </div>

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

                    This is an automated email from the APP System.

                    <br><br>

                    Please do not reply directly to this email.

                </div>

            </div>

        </div>

    </div>

</body>

</html>
