<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Approval Pending Summary</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, Helvetica, sans-serif; background-color:#f4f6f9;">

@php
    $name = $name ?? 'User';
    $total = $total ?? 0;
    $documents = $documents ?? [];
@endphp

<table width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f6f9; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="900" cellspacing="0" cellpadding="0" style="width:900px; max-width:900px; background:#ffffff; border-radius:10px; overflow:hidden; border-collapse:collapse;">

                <tr>
                    <td style="background:#f59e0b; padding:22px 24px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0; font-size:22px; font-weight:bold;">
                            Approval Pending Summary
                        </h2>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px; color:#333333; font-size:14px; line-height:1.7;">
                        <p style="margin-top:0;">Halo <strong>{{ $name }}</strong>,</p>

                        <p>
                            Anda memiliki total
                            <strong style="color:#f59e0b;">{{ $total }}</strong>
                            dokumen yang masih menunggu approval.
                        </p>

                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-top:20px; font-size:13px;">
                            <thead>
                                <tr>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">No</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Document</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Document ID</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Company</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Department</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Previous Approval Date</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb; padding:10px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documents as $index => $doc)
                                    <tr>
                                        <td style="border:1px solid #e5e7eb; padding:9px; text-align:center;">
                                            {{ $index + 1 }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px;">
                                            {{ $doc['docname'] ?? '-' }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px;">
                                            {{ $doc['docid'] ?? '-' }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px;">
                                            {{ $doc['cpnyid'] ?? '-' }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px;">
                                            {{ $doc['deptname'] ?? '-' }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px;">
                                            {{ $doc['date'] ?? '-' }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb; padding:9px; text-align:center;">
                                            <a href="{{ $doc['url'] ?? '#' }}" target="_blank"
                                               style="background:#f59e0b; color:#ffffff; text-decoration:none; padding:7px 12px; border-radius:5px; font-weight:bold; display:inline-block;">
                                                Open Link
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" style="border:1px solid #e5e7eb; padding:12px; text-align:center;">
                                            Tidak ada dokumen pending.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

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