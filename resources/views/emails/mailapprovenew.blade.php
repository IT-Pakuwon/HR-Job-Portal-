<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Approval Notification</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

@php
    // Fallback variabel agar tidak error jika tidak dikirim dari controller
    $docname  = $docname  ?? 'SPPB';
    $name = $name ?? ($name ?? 'User');
    $status   = strtoupper($status ?? 'P'); // P,R,D,A,C

    $map = [
        'P' => [
            'title'    => 'Waiting Approval',
            'banner'   => '#f59e0b', // amber
            'btnText'  => 'Review & Approve Now →',
            'btnColor' => '#f59e0b',
            'intro'    => "Anda memiliki dokumen {$docname} yang menunggu approval.",
        ],
        'R' => [
            'title'    => 'Rejected Approval',
            'banner'   => '#ef4444', // red
            'btnText'  => 'View Detail →',
            'btnColor' => '#ef4444',
            'intro'    => "Dokumen {$docname} telah ditolak.",
        ],
        'D' => [
            'title'    => 'Revise Approval',
            'banner'   => '#f97316', // orange
            'btnText'  => 'Open & Revise →',
            'btnColor' => '#f97316',
            'intro'    => "Dokumen {$docname} memerlukan revisi.",
        ],
        'A' => [
            'title'    => 'Approved',
            'banner'   => '#10b981', // green
            'btnText'  => 'View Document →',
            'btnColor' => '#10b981',
            'intro'    => "Dokumen {$docname} telah disetujui.",
        ],
        'C' => [
            'title'    => 'Completed',
            'banner'   => '#10b981', // green
            'btnText'  => 'View Document →',
            'btnColor' => '#10b981',
            'intro'    => "Dokumen {$docname} telah selesai diproses.",
        ],
    ];
    $cfg = $map[$status] ?? $map['P'];
@endphp

<!-- Wrapper -->
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f9; padding:40px 0;">
  <tr>
    <td align="center">
      <!-- Card -->
      <table width="600" cellspacing="0" cellpadding="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
          <td style="background:{{ $cfg['banner'] }}; padding:20px; text-align:center; color:#ffffff;">
            <h2 style="margin:0; font-size:22px;">{{ $docname }} — {{ $cfg['title'] }}</h2>
          </td>
        </tr>

        <!-- Body -->
        <tr>
          <td style="padding:30px; color:#333333; font-size:14px; line-height:1.6;">
            <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>
            <p>{{ $cfg['intro'] }}</p>

            <table cellspacing="0" cellpadding="6" style="width:100%; margin:20px 0; border:1px solid #e0e0e0; border-radius:6px;">
                <tr>
                    <td style="background:#f9fafb; font-weight:bold;">Doc ID</td>
                    <td>{{ $docid }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Status</td>
                    <td>{{ $cfg['title'] }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold; width:30%;">Info</td>
                    <td>{{ $info }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold;">Company</td>
                    <td>{{ $cpnyid }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold;">Department</td>
                    <td>{{ $deptname }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold;">Date</td>
                    <td>{{ $date }}</td>
                </tr>
                <tr>
                    <td style="background:#f9fafb; font-weight:bold;">Created By</td>
                    <td>{{ $createdby }}</td>
                </tr>

            </table>

            <p style="text-align:center; margin:30px 0;">
              <a href="{{ $url }}" target="_blank"
                 style="background:{{ $cfg['btnColor'] }}; color:#ffffff; text-decoration:none; padding:12px 24px; border-radius:6px; font-weight:bold; display:inline-block;">
                 {{ $cfg['btnText'] }}
              </a>
            </p>

            <p style="margin-top:30px; font-size:12px; color:#888888; text-align:center;">
              Email ini dikirim secara otomatis oleh sistem Pakuwon.<br>
              Jangan membalas email ini.
            </p>
          </td>
        </tr>
      </table>
      <!-- End Card -->
    </td>
  </tr>
</table>

</body>
</html>
