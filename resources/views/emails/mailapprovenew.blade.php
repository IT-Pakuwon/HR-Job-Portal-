<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Notification</title>
</head>

@php
    // Fallback variabel agar tidak error jika tidak dikirim dari controller
    $docid = $docid ?? '-';
    $docname = $docname ?? 'SPPB';
    $name = $name ?? 'User';
    $status = strtoupper($status ?? 'P'); // P,R,D,A,C,X
    $info = $info ?? '-';
    $cpnyid = $cpnyid ?? '-';
    $deptname = $deptname ?? '-';
    $date = $date ?? '-';
    $createdby = $createdby ?? '-';
    $actionby = $actionby ?? null;
    $url = $url ?? '#';
    $tenant = $tenant ?? null;
    $voucherLines = $voucher_lines ?? [];
    $showNominal = !empty($voucherLines) && array_key_exists('nominal', $voucherLines[0]);

    // banner = warna utama, dark = ujung gradasi header, soft = warna lembut untuk label/kotak
    $map = [
        'P' => [
            'title' => 'Waiting Approval', 'icon' => '⏳',
            'banner' => '#f59e0b', 'dark' => '#b45309', 'soft' => '#fffbeb', 'line' => '#fde68a', 'text' => '#92400e',
            'btnText' => 'Review & Approve Now →',
            'intro' => "Anda memiliki dokumen {$docname} yang menunggu approval.",
        ],
        'R' => [
            'title' => 'Rejected Approval', 'icon' => '✖',
            'banner' => '#ef4444', 'dark' => '#b91c1c', 'soft' => '#fef2f2', 'line' => '#fecaca', 'text' => '#991b1b',
            'btnText' => 'View Detail →',
            'intro' => "Dokumen {$docname} telah ditolak.",
        ],
        'D' => [
            'title' => 'Revise Approval', 'icon' => '✏️',
            'banner' => '#f97316', 'dark' => '#c2410c', 'soft' => '#fff7ed', 'line' => '#fed7aa', 'text' => '#9a3412',
            'btnText' => 'Open & Revise →',
            'intro' => "Dokumen {$docname} memerlukan revisi.",
        ],
        'A' => [
            'title' => 'Approved', 'icon' => '✔',
            'banner' => '#10b981', 'dark' => '#047857', 'soft' => '#ecfdf5', 'line' => '#a7f3d0', 'text' => '#065f46',
            'btnText' => 'View Document →',
            'intro' => "Dokumen {$docname} telah disetujui.",
        ],
        'C' => [
            'title' => 'Completed', 'icon' => '✅',
            'banner' => '#10b981', 'dark' => '#047857', 'soft' => '#ecfdf5', 'line' => '#a7f3d0', 'text' => '#065f46',
            'btnText' => 'View Document →',
            'intro' => "Dokumen {$docname} telah selesai diproses.",
        ],
        'X' => [
            'title' => 'Cancelled', 'icon' => '🚫',
            'banner' => '#6b7280', 'dark' => '#374151', 'soft' => '#f3f4f6', 'line' => '#d1d5db', 'text' => '#374151',
            'btnText' => 'View Document →',
            'intro' => "Dokumen {$docname} telah dibatalkan.",
        ],
    ];
    $cfg = $map[$status] ?? $map['P'];

    $rows = [
        ['label' => 'Doc ID', 'value' => $docid],
        ['label' => 'Status', 'value' => $cfg['title']],
        ['label' => 'Info', 'value' => $info],
        ['label' => 'Company', 'value' => $cpnyid],
        ['label' => 'Department', 'value' => $deptname],
    ];
    if ($tenant) {
        $rows[] = ['label' => 'Tenant', 'value' => $tenant];
    }
    $rows[] = ['label' => 'Date', 'value' => $date];
    $rows[] = ['label' => 'Created By', 'value' => $createdby];
    if ($actionby) {
        $rows[] = [
            'label' => $status === 'X' ? 'Cancelled By' : ($status === 'D' ? 'Revised By' : 'Action By'),
            'value' => $actionby,
        ];
    }
@endphp

<body style="margin:0;padding:0;background:#eef1f6;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

    {{-- Preheader: hidden preview text shown in inbox lists, not in the body --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">
        {{ $docid }} — {{ $cfg['title'] }}: {{ $info }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef1f6;padding:40px 16px;">
        <tr>
            <td align="center" valign="top">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:{{ $cfg['banner'] }};background:linear-gradient(135deg,{{ $cfg['dark'] }} 0%,{{ $cfg['banner'] }} 100%);border-radius:14px 14px 0 0;padding:30px 36px 26px;">
                            <p style="margin:0 0 14px;display:inline-block;background:rgba(255,255,255,0.2);border-radius:999px;padding:3px 12px;font-size:10px;font-weight:700;color:#ffffff;letter-spacing:0.12em;text-transform:uppercase;">
                                Pakuwon System
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="background:rgba(255,255,255,0.22);border-radius:10px;width:44px;height:44px;text-align:center;vertical-align:middle;font-size:20px;color:#ffffff;">
                                        {{ $cfg['icon'] }}
                                    </td>
                                    <td width="14"></td>
                                    <td valign="middle">
                                        <h1 style="margin:0 0 3px;font-size:21px;font-weight:800;color:#ffffff;line-height:1.25;letter-spacing:-0.01em;">
                                            {{ $cfg['title'] }}
                                        </h1>
                                        <p style="margin:0;font-size:12.5px;color:#ffffff;opacity:0.9;line-height:1.5;">
                                            {{ $docname }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background:#ffffff;padding:34px 36px;font-size:14px;line-height:1.65;">
                            <p style="margin:0 0 12px;">Halo <strong>{{ $name }}</strong>,</p>
                            <p style="margin:0 0 4px;">{{ $cfg['intro'] }}</p>

                            @include('emails.partials.detail-table', ['rows' => $rows])

                            @if (count($voucherLines))
                                <p style="margin:24px 0 8px;font-size:12.5px;font-weight:800;color:#334155;">Voucher Detail</p>
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0;border-radius:8px;border-collapse:separate;overflow:hidden;margin:0 0 18px;">
                                    <tr>
                                        <td style="padding:9px 12px;background:#f1f5f9;font-size:11.5px;font-weight:700;color:#475569;">Voucher</td>
                                        <td style="padding:9px 12px;background:#f1f5f9;font-size:11.5px;font-weight:700;color:#475569;">Qty</td>
                                        @if ($showNominal)
                                            <td style="padding:9px 12px;background:#f1f5f9;font-size:11.5px;font-weight:700;color:#475569;">Total Nominal</td>
                                        @endif
                                        <td style="padding:9px 12px;background:#f1f5f9;font-size:11.5px;font-weight:700;color:#475569;">Exp Date</td>
                                    </tr>
                                    @foreach ($voucherLines as $line)
                                        <tr>
                                            <td style="padding:9px 12px;border-top:1px solid #e2e8f0;font-size:13px;">{{ $line['name'] }}</td>
                                            <td style="padding:9px 12px;border-top:1px solid #e2e8f0;font-size:13px;">{{ $line['qty'] }}</td>
                                            @if ($showNominal)
                                                <td style="padding:9px 12px;border-top:1px solid #e2e8f0;font-size:13px;">Rp {{ $line['nominal'] }}</td>
                                            @endif
                                            <td style="padding:9px 12px;border-top:1px solid #e2e8f0;font-size:13px;">{{ $line['exp'] }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:26px 0 4px;">
                                <tr>
                                    <td style="border-radius:8px;background:{{ $cfg['banner'] }};">
                                        <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:13px 30px;font-size:13.5px;font-weight:700;color:#ffffff;text-decoration:none;border-radius:8px;">
                                            {{ $cfg['btnText'] }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 14px 14px;padding:18px 36px;">
                            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.7;">
                                Email ini dikirim secara otomatis oleh sistem <strong style="color:#64748b;">Pakuwon</strong>.<br>
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
