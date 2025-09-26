<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Talent Acquisition Pakuwon Group Jakarta</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px;">
        <h2>Dear {{ $name }},</h2>

        <br>

        <p>Through this message I would like inform and congrats you to continue next interview processes <strong>(User interview & Psychotest).</strong></p>
        <p>Below are detail schedule for User Interview & Psychotest:</strong></p>
        {{-- <p><strong>Date:</strong> {{ $startdate }}</p>
        <p><strong>Time:</strong> {{ $starttime }} – {{ $endtime }} WIB (Western Indonesia Time)</p>
        <p><strong>Location:</strong> {{ $location }} – {{ $address }}</p> --}}
        <table cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:720px;border:1px solid #e5e7eb;border-collapse:collapse;">
        
        <tr>
            <td style="padding:8px;background:#f9fafb;">Day & Date</td>
            <td style="padding:8px;">: {{ $interview_date }}</td>
        </tr>
        <tr>
            <td style="padding:8px;background:#f9fafb;">Time</td>
            <td style="padding:8px;">: {{ $starttime }} – {{ $endtime }} WIB (Western Indonesia Time)</td>
        </tr>
        <tr>
            <td style="padding:8px;background:#f9fafb;">Venue</td>
            <td style="padding:8px;">: {{ $location }}</td>
        </tr>
        <tr>
            <td style="padding:8px;background:#f9fafb;"></td>
            <td style="padding:8px;"> {{ $address }}</td>
        </tr>
        <tr>
            <td style="padding:8px;background:#f9fafb;">PIC Recruitment</td>
            <td style="padding:8px;">:  Adela / Frengky</td>
        </tr>
        </table>

        <p>Notes: Obligated to bring your own stationary (pen & pencil) with dresscode formal attire</p>

        <p>Kindly confirm your attendance by replying with Attend / Not Attend / Reschedule via WhatsApp to <strong>+62 858 9001 4129</strong>.
If you have any questions, feel free to contact us via email.</p>

<p>Thank you, and we look forward to meeting you.</p>

        

        {{-- <p style="margin-top: 30px;">Please confirm by replying with Attend / Not Attend / Reschedule to email: <strong>recruitment@pakuwon.com</strong>
. If you have any questions, feel free to contact us via email. Thank you.</p><br> --}}

        <p>Warm regards,</p>
        <p><strong>Talent Acquisition Pakuwon Group Jakarta</strong></p>
    </div>
</body>
</html>
