<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Waiting List {{ $accepted ? 'Diterima' : 'Ditolak' }}</title>
</head>

<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

    @php
        $banner = $accepted ? '#10b981' : '#ef4444';
        $title = $accepted ? 'Slot Diterima' : 'Slot Ditolak';
        $verb = $accepted ? 'menerima' : 'menolak';
    @endphp

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
                        <td style="background:{{ $banner }}; padding:20px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0; font-size:22px;">{{ $docid }} — {{ $title }}</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                            <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
                            <p><strong>{{ $participant_name }}</strong> telah {{ $verb }} slot waiting list untuk training <strong>{{ $training_name }}</strong>.</p>

                            <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0;">
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Peserta</td>
                                    <td>{{ $participant_name }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Training</td>
                                    <td>{{ $training_name }}</td>
                                </tr>
                                @if ($schedule_date)
                                <tr>
                                    <td style="background:#f9fafb; font-weight:bold;">Tanggal</td>
                                    <td>{{ \Carbon\Carbon::parse($schedule_date)->format('d M Y') }}</td>
                                </tr>
                                @endif
                            </table>

                            @if (!$accepted)
                            <p>Slot ini akan otomatis ditawarkan ke orang berikutnya di waiting list.</p>
                            @endif

                            <p style="text-align:center; margin:30px 0;">
                                <a href="{{ $url }}" target="_blank"
                                    style="background:{{ $banner }}; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                    Lihat Detail →
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
