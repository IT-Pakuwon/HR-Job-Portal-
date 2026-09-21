<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Training Slot Available</title>
</head>

<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

    <table width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f9;">
        <tr>
            <td align="left" style="margin: 0; padding: 0">
                <table width="100%" cellspacing="0" cellpadding="0"
                    style="
                            background: #ffffff;
                            color: #111827;
                            font-family: Arial, Helvetica, sans-serif;
                            line-height: 1.6;
                            border-collapse: collapse;
        ">
                    <tr>
                        <td style="background:#0ea5e9; padding:20px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0; font-size:22px;">{{ $docid }} — Slot Available</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                            <p style="margin-top:0;">Hello <strong>{{ $name }}</strong>,</p>
                            <p>A slot for the training <strong>{{ $training_name }}</strong>, previously full, is now available, and you are next in line on the waiting list.</p>

                            <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0;">
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Training</td>
                                    <td>{{ $training_name }}</td>
                                </tr>
                                @if ($schedule_date)
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Date</td>
                                    <td>{{ \Carbon\Carbon::parse($schedule_date)->format('d M Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Confirm By</td>
                                    <td>{{ $expires_at ? \Carbon\Carbon::parse($expires_at)->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            </table>

                            <p>Please confirm within <strong>24 hours</strong>. If not confirmed, this slot will automatically be offered to the next person on the waiting list.</p>

                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}" target="_blank"
                                    style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                    Confirm Now →
                                </a>
                            </p>

                            <p style="margin-top:30px; font-size:12px; color:#888888; text-align:center;">
                                This email was sent automatically by the Pakuwon system.<br>
                                Please do not reply to this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
