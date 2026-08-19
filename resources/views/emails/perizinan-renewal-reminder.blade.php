<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit Renewal Reminder</title>
</head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#1f2937">
    <div style="max-width:760px;margin:24px auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">
        <div style="padding:22px 26px;background:#4f46e5;color:#ffffff">
            <h2 style="margin:0;font-size:20px">Permit Renewal Reminder</h2>
        </div>
        <div style="padding:26px">
            <p>Hello {{ $name }},</p>
            <p>The following permit(s) are approaching their expiration date. Please create the renewal before they expire.</p>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;font-size:13px">
                <thead>
                    <tr style="background:#f9fafb">
                        <th style="border:1px solid #e5e7eb;padding:10px;text-align:left">Permit ID</th>
                        <th style="border:1px solid #e5e7eb;padding:10px;text-align:left">Title</th>
                        <th style="border:1px solid #e5e7eb;padding:10px;text-align:left">Company / Site</th>
                        <th style="border:1px solid #e5e7eb;padding:10px;text-align:left">End Date</th>
                        <th style="border:1px solid #e5e7eb;padding:10px;text-align:center">Remaining</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($documents as $document)
                        <tr>
                            <td style="border:1px solid #e5e7eb;padding:10px"><strong>{{ $document['perizinan_id'] }}</strong></td>
                            <td style="border:1px solid #e5e7eb;padding:10px">{{ $document['title'] }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px">{{ $document['company'] }} - {{ $document['site'] }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px">{{ $document['end_date'] }}</td>
                            <td style="border:1px solid #e5e7eb;padding:10px;text-align:center"><strong>{{ $document['days_remaining'] }} days</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="margin:24px 0">
                <a href="{{ $permitUrl }}" style="display:inline-block;padding:11px 18px;border-radius:8px;background:#4f46e5;color:#ffffff;text-decoration:none;font-weight:bold">Open Permit Monitoring</a>
            </p>
            <p style="margin-bottom:0;color:#6b7280;font-size:12px">Further reminders will stop automatically after a renewal permit referencing the original Permit ID has been created.</p>
        </div>
    </div>
</body>
</html>
