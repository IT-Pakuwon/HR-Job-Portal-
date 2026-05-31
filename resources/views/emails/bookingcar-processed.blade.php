<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Car — Process Notification</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

@php
    $isCancelled = !empty($status_perjalanan);
    $bannerColor  = $isCancelled ? '#ef4444' : '#6366f1';
    $bannerTitle  = $isCancelled ? 'Booking Request — Status Updated' : 'Booking Car — Processed by GA';
    $intro = $isCancelled
        ? "Mohon maaf, permintaan Booking Car Anda telah diperbarui oleh GA dengan status: <strong>{$status_perjalanan}</strong>."
        : "Permintaan Booking Car Anda telah diproses oleh GA. Berikut detail kendaraan dan pengemudi yang ditugaskan.";
    $btnColor = $isCancelled ? '#ef4444' : '#6366f1';
@endphp

<table width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f9;">
    <tr>
        <td align="left" style="margin:0; padding:0;">
            <table width="100%" cellspacing="0" cellpadding="0"
                style="background:#ffffff; color:#111827; font-family:Arial, Helvetica, sans-serif; line-height:1.6; border-collapse:collapse;">

                <!-- Header -->
                <tr>
                    <td style="background:{{ $bannerColor }}; padding:20px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0; font-size:22px;">{{ $bannerTitle }}</h2>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
                        <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
                        <p>{!! $intro !!}</p>

                        <!-- Doc Info -->
                        <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0; border-collapse:collapse;">
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; width:35%; padding:8px;">Doc ID</td>
                                <td style="padding:8px;">{{ $docid }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">Date Used</td>
                                <td style="padding:8px;">{{ $date }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">Purpose</td>
                                <td style="padding:8px;">{{ $info }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">Company</td>
                                <td style="padding:8px;">{{ $cpnyid }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">Department</td>
                                <td style="padding:8px;">{{ $deptname }}</td>
                            </tr>
                        </table>

                        @if($isCancelled)
                        <!-- Status Perjalanan highlight -->
                        <table cellspacing="0" cellpadding="0" style="width:100%; margin:20px 0;">
                            <tr>
                                <td style="background:#fef2f2; border-left:4px solid #ef4444; padding:14px 16px; border-radius:4px;">
                                    <p style="margin:0; font-weight:bold; color:#b91c1c; font-size:13px;">STATUS PERJALANAN</p>
                                    <p style="margin:4px 0 0; font-size:15px; color:#111827;">{{ $status_perjalanan }}</p>
                                </td>
                            </tr>
                        </table>
                        @else
                        <!-- Driver & Vehicle -->
                        <p style="font-weight:bold; color:#374151; margin-bottom:8px;">Detail Penugasan</p>
                        <table cellspacing="0" cellpadding="6" style="width:100%; margin:0 0 20px; border-collapse:collapse;">
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; width:35%; padding:8px;">Driver</td>
                                <td style="padding:8px;">{{ $driver ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">Handphone</td>
                                <td style="padding:8px;">{{ $handphone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td style="background:#f9fafb; font-weight:bold; padding:8px;">No. Polisi</td>
                                <td style="padding:8px;">{{ $no_polisi ?: '-' }}</td>
                            </tr>
                        </table>
                        @endif

                        <!-- CTA -->
                        <p style="text-align:center; margin:30px 0;">
                            <a href="{{ $url }}" target="_blank"
                                style="background:{{ $btnColor }}; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                View Document →
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
