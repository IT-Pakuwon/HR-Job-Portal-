<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit {{ $isEdit ? 'Updated' : 'Created' }}</title>
</head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937">
    <div style="max-width:680px;margin:24px auto;overflow:hidden;border:1px solid #e5e7eb;border-radius:12px;background:#ffffff">
        <div style="padding:22px 26px;background:#4f46e5;color:#ffffff">
            <h2 style="margin:0;font-size:20px">Permit {{ $isEdit ? 'Updated' : 'Created' }}</h2>
        </div>
        <div style="padding:26px">
            <p>Hello {{ $recipientName }},</p>
            <p>Your permit has been successfully {{ $isEdit ? 'updated' : 'created' }}.</p>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:14px">
                <tr><td style="width:180px;border-bottom:1px solid #e5e7eb;padding:9px 0;color:#6b7280">Permit ID</td><td style="border-bottom:1px solid #e5e7eb;padding:9px 0"><strong>{{ $permit->perizinan_id }}</strong></td></tr>
                <tr><td style="border-bottom:1px solid #e5e7eb;padding:9px 0;color:#6b7280">Title</td><td style="border-bottom:1px solid #e5e7eb;padding:9px 0">{{ $permit->perizinan_title ?: '-' }}</td></tr>
                <tr><td style="border-bottom:1px solid #e5e7eb;padding:9px 0;color:#6b7280">Company</td><td style="border-bottom:1px solid #e5e7eb;padding:9px 0">{{ $permit->cpny_id ?: '-' }}</td></tr>
                <tr><td style="border-bottom:1px solid #e5e7eb;padding:9px 0;color:#6b7280">Department</td><td style="border-bottom:1px solid #e5e7eb;padding:9px 0">{{ $permit->department_fin_id ?: '-' }}</td></tr>
                <tr><td style="border-bottom:1px solid #e5e7eb;padding:9px 0;color:#6b7280">Start Date</td><td style="border-bottom:1px solid #e5e7eb;padding:9px 0">{{ $permit->startdate ?: '-' }}</td></tr>
                <tr><td style="padding:9px 0;color:#6b7280">End Date</td><td style="padding:9px 0">{{ $permit->expired_date ? ($permit->enddate ?: '-') : 'No expiration date' }}</td></tr>
            </table>

            <p style="margin:24px 0 0">
                <a href="{{ $permitUrl }}" style="display:inline-block;border-radius:8px;background:#4f46e5;padding:11px 18px;color:#ffffff;text-decoration:none;font-weight:bold">Open Permit Details</a>
            </p>
        </div>
    </div>
</body>
</html>
