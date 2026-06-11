<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
</head>
<body style="margin:0;padding:0;background:#e8edf5;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#e8edf5;padding:40px 16px;">
        <tr>
            <td align="center" valign="top">

                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;">

                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#1e3a8a 0%,#1d4ed8 55%,#3b82f6 100%);border-radius:16px 16px 0 0;padding:32px 40px 28px;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <p style="margin:0 0 16px;display:inline-block;background:rgba(255,255,255,0.15);border-radius:999px;padding:3px 12px;font-size:10px;font-weight:700;color:#bfdbfe;letter-spacing:0.12em;text-transform:uppercase;">
                                            IT Ticketing System
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td style="background:rgba(255,255,255,0.18);border-radius:12px;width:48px;height:48px;text-align:center;vertical-align:middle;font-size:22px;">
                                                    @yield('icon')
                                                </td>
                                                <td width="16"></td>
                                                <td valign="middle">
                                                    <h1 style="margin:0 0 4px;font-size:22px;font-weight:800;color:#ffffff;line-height:1.25;letter-spacing:-0.01em;">
                                                        @yield('header')
                                                    </h1>
                                                    <p style="margin:0;font-size:13px;color:#93c5fd;line-height:1.5;">
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
                        <td style="background:#ffffff;padding:32px 40px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 16px 16px;padding:18px 40px;">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td>
                                        <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.7;">
                                            This is an automated email from <strong style="color:#64748b;">IT Ticketing System</strong>.<br>
                                            Please do not reply directly to this email.
                                        </p>
                                    </td>
                                    <td align="right" valign="middle">
                                        <p style="margin:0;font-size:11px;font-weight:700;color:#cbd5e1;">Pakuwon Group</p>
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
