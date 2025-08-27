<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pakuwon Careers</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px;">
        <h2>Dear {{ $name }},</h2>

        <br>

        <p>We are pleased to invite you for an interview for the <strong>{{ $jobtitle }}</strong> position, scheduled as follows:</p>
        <p><strong>Date:</strong> {{ $startdate }}</p>
        <p><strong>Time:</strong> {{ $starttime }} – {{ $endtime }} WIB (Western Indonesia Time)</p>
        <p><strong>Location:</strong> {{ $location }} – {{ $address }}</p>

        <p>
            After receiving this email, please confirm your attendance by replying to this message.
            <br>Thank you for your attention.
        </p>

        <p style="margin-top: 30px;">If you have any questions, feel free to contact us by replying to this email.</p><br>

        <p>Warm regards,</p>
        <p><strong>Pakuwon Careers Team</strong></p>
    </div>
</body>
</html>
