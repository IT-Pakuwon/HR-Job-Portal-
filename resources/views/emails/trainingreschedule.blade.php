<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Jadwal Training Diubah</title>
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
                        <td style="background:#f59e0b; padding:20px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0; font-size:22px;">{{ $docid }} — Jadwal Diubah</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                            <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
                            <p>Jadwal training <strong>{{ $training_name }}</strong> yang Anda ikuti telah diubah.</p>

                            <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0;">
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Training</td>
                                    <td>{{ $training_name }}</td>
                                </tr>
                                @if ($old_date)
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Tanggal Lama</td>
                                    <td style="text-decoration:line-through; color:#888888;">{{ \Carbon\Carbon::parse($old_date)->format('d M Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Tanggal Baru</td>
                                    <td><strong>{{ \Carbon\Carbon::parse($new_date)->format('d M Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Alasan</td>
                                    <td>{{ $reason }}</td>
                                </tr>
                            </table>

                            <p>Seat/waiting list Anda tetap dipertahankan pada tanggal baru. Jika Anda tidak dapat hadir pada tanggal baru ini, silakan batalkan registrasi Anda melalui tautan di bawah.</p>

                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}" target="_blank"
                                    style="background:#111827; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                    Lihat Registrasi Saya →
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
