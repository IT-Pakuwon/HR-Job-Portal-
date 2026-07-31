<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reminder Submit RFP Kontrak</title>
</head>
<body style="margin:0; padding:0; background:#f3f4f6; font-family:Arial, sans-serif; color:#111827;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table width="760" cellpadding="0" cellspacing="0" style="background:#ffffff; border:1px solid #e5e7eb;">
                    <tr>
                        <td style="padding:18px 22px; background:#1f2937; color:#ffffff;">
                            <h2 style="margin:0; font-size:18px;">Reminder Submit RFP Kontrak</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:20px 22px; font-size:14px; line-height:1.5;">
                            <p style="margin:0 0 10px;">Dear {{ $name }},</p>
                            <p style="margin:0 0 16px;">
                                Berikut RFP Kontrak yang masih perlu disubmit.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; font-size:12px;">
                                <thead>
                                    <tr>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">RFP ID</th>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Company</th>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Department</th>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Date</th>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Vendor</th>
                                        <th align="left" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Purpose</th>
                                        <th align="right" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Amount</th>
                                        <th align="center" style="padding:8px; border:1px solid #e5e7eb; background:#f9fafb;">Link</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documents as $doc)
                                        <tr>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['rfp_id'] }}</td>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['cpny_id'] }}</td>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['department_id'] }}</td>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['rfp_date'] }}</td>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['vendor_name'] }}</td>
                                            <td style="padding:8px; border:1px solid #e5e7eb;">{{ $doc['purpose'] }}</td>
                                            <td align="right" style="padding:8px; border:1px solid #e5e7eb;">
                                                {{ number_format($doc['amount'], 2, ',', '.') }}
                                            </td>
                                            <td align="center" style="padding:8px; border:1px solid #e5e7eb;">
                                                <a href="{{ $doc['url'] }}" style="color:#2563eb; text-decoration:none;">Open</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <p style="margin:16px 0 0; color:#6b7280; font-size:12px;">
                                Email ini dikirim secara otomatis oleh sistem Pakuwon. Mohon jangan membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
