<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Notification</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

    @php
        $docname   = $docname ?? 'Document';
        $name      = $name ?? 'User';
        $docid     = $docid ?? '-';
        $cpnyid    = $cpnyid ?? '-';
        $deptname  = $deptname ?? '-';
        $date      = $date ?? '-';
        $createdby = $createdby ?? '-';
        $info      = $info ?? '-';
        $url       = $url ?? '#';
        $status    = strtoupper($status ?? 'P');

        $map = [
            'P' => [
                'title'    => 'Waiting Approval',
                'banner'   => '#f59e0b',
                'btnText'  => 'Review & Approve Now',
                'btnColor' => '#f59e0b',
                'intro'    => "Anda memiliki dokumen {$docname} yang menunggu approval.",
            ],
            'R' => [
                'title'    => 'Rejected',
                'banner'   => '#ef4444',
                'btnText'  => 'View Detail',
                'btnColor' => '#ef4444',
                'intro'    => "Dokumen {$docname} telah ditolak.",
            ],
            'D' => [
                'title'    => 'Need Revision',
                'banner'   => '#f97316',
                'btnText'  => 'Open & Revise',
                'btnColor' => '#f97316',
                'intro'    => "Dokumen {$docname} memerlukan revisi.",
            ],
            'A' => [
                'title'    => 'Approved',
                'banner'   => '#10b981',
                'btnText'  => 'View Document',
                'btnColor' => '#10b981',
                'intro'    => "Dokumen {$docname} telah disetujui.",
            ],
            'C' => [
                'title'    => 'Completed',
                'banner'   => '#10b981',
                'btnText'  => 'View Document',
                'btnColor' => '#10b981',
                'intro'    => "Dokumen {$docname} telah selesai diproses.",
            ],
        ];

        $cfg = $map[$status] ?? $map['P'];
    @endphp

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f4f6f9; margin:0; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="700" border="0" cellspacing="0" cellpadding="0" style="width:700px; max-width:700px; background:#ffffff; border-radius:10px; overflow:hidden; border-collapse:collapse;">
                    
                    <tr>
                        <td style="background:{{ $cfg['banner'] }}; padding:22px 24px; text-align:center; color:#ffffff;">
                            <h2 style="margin:0; font-size:22px; font-weight:bold;">
                                {{ $docname }} - {{ $cfg['title'] }}
                            </h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:30px 28px; color:#333333; font-size:14px; line-height:1.7;">
                            <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
                            <p style="margin:0 0 18px 0;">{{ $cfg['intro'] }}</p>

                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin:20px 0;">
                                <tr>
                                    <td style="width:180px; background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Document ID</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $docid }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Status</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $cfg['title'] }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Information</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $info }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Company</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $cpnyid }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Department</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $deptname }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Approval Date</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $date }}</td>
                                </tr>
                                <tr>
                                    <td style="background:#f9fafb; padding:10px 12px; font-weight:bold; border:1px solid #e5e7eb;">Created By</td>
                                    <td style="padding:10px 12px; border:1px solid #e5e7eb;">{{ $createdby }}</td>
                                </tr>
                            </table>

                            <div style="text-align:center; margin:28px 0 10px 0;">
                                <a href="{{ $url }}" target="_blank"
                                   style="background:{{ $cfg['btnColor'] }}; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                                    {{ $cfg['btnText'] }}
                                </a>
                            </div>

                            <p style="margin-top:28px; font-size:12px; color:#6b7280; text-align:center;">
                                Email ini dikirim secara otomatis oleh sistem Pakuwon.<br>
                                Mohon jangan membalas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>