<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Pakuwon System')</title>
</head>
<body style="margin:0;padding:0;background:#eef1f6;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

    {{-- Preheader: hidden preview text shown in inbox lists, not in the body --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">
        @yield('preheader', ' ')
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef1f6;padding:40px 16px;">
        <tr>
            <td align="center" valign="top">

                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background-color:#334155;background:linear-gradient(135deg,#1e293b 0%,#334155 55%,#475569 100%);border-radius:14px 14px 0 0;padding:30px 36px 26px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 14px;display:inline-block;background:rgba(255,255,255,0.15);border-radius:999px;padding:3px 12px;font-size:10px;font-weight:700;color:#cbd5e1;letter-spacing:0.12em;text-transform:uppercase;">
                                            {{ $systemLabel ?? 'Pakuwon System' }}
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="background:rgba(255,255,255,0.18);border-radius:10px;width:44px;height:44px;text-align:center;vertical-align:middle;font-size:20px;">
                                                    @yield('icon', '🔔')
                                                </td>
                                                <td width="14"></td>
                                                <td valign="middle">
                                                    <h1 style="margin:0 0 3px;font-size:21px;font-weight:800;color:#ffffff;line-height:1.25;letter-spacing:-0.01em;">
                                                        @yield('header')
                                                    </h1>
                                                    <p style="margin:0;font-size:12.5px;color:#cbd5e1;line-height:1.5;">
                                                        @yield('subtitle')
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background:#ffffff;padding:34px 36px;font-size:14px;line-height:1.65;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 14px 14px;padding:18px 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.7;">
                                            This is an automated email from <strong style="color:#64748b;">{{ $systemLabel ?? 'Pakuwon System' }}</strong>.<br>
                                            Please do not reply directly to this email.
                                        </p>
                                    </td>
                                    <td align="right" valign="middle">
                                        <p style="margin:0;font-size:11px;font-weight:700;color:#cbd5e1;">Pakuwon System</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
