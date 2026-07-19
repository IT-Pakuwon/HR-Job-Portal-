<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Slot Training Tersedia</title>
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
                            <h2 style="margin:0; font-size:22px;">{{ $docid }} — Slot Tersedia</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                            <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
                            <p>Sebuah slot untuk training <strong>{{ $training_name }}</strong> yang sebelumnya penuh kini tersedia, dan Anda adalah antrian berikutnya di waiting list.</p>

                            <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0;">
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Training</td>
                                    <td>{{ $training_name }}</td>
                                </tr>
                                @if ($schedule_date)
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Tanggal</td>
                                    <td>{{ \Carbon\Carbon::parse($schedule_date)->format('d M Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Batas Konfirmasi</td>
                                    <td>{{ $expires_at ? \Carbon\Carbon::parse($expires_at)->format('d M Y H:i') : '-' }}</td>
                                </tr>
                            </table>

                            <p>Silakan konfirmasi dalam waktu <strong>24 jam</strong>. Jika tidak dikonfirmasi, slot ini akan otomatis ditawarkan ke orang berikutnya di waiting list.</p>

                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}" target="_blank"
                                    style="background:#0ea5e9; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                    Konfirmasi Sekarang →
                                </a>
                            </p>

                            <p style="margin-top:30px; font-size:12px; color:#888888; text-align:center;">
                                Email ini dikirim secara otomatis oleh sistem Pakuwon.<br>
                                Jangan membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>

</html>
