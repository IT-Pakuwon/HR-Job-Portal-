<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pakuwon Career</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f5f5f5; padding: 20px;">
    <div style="background-color: #ffffff; padding: 30px; border-radius: 10px;">
        @php
            $name = $name ?? 'Candidate';
            $job = $job_title ?? 'your application';
            $company = $company ?? 'Pakuwon Group Jakarta';
        @endphp
        <h2>Dear {{ $name }},</h2><br>

        <p>Thank you for your interest in <strong>{{ $company }}</strong> and making your time available for our recruitment process. </p>
        <p>We are very grateful that we have so many interested candidates and that means we have to make some tough</p>
        <p>choices from a large number of applicants. You have come this far, and we think it’s already an achievement!</p>
        <p><strong>Unfortunately, we could not proceed with your application for the next step.</strong></p>
       
        <p>But don't worry, we'll keep your profile and resume in our database and consider it for future opportunities.</p>
       
        <p><strong>Wishing you for best endeavors in future !!</strong></p><br> 
       
        <p><strong>Warm regards,</strong><p>
        <p><strong>Talent Acquisition {{ $company }}</strong><p>

        <hr>
        <p style="font-size:12px;color:#666;">
        You can visit our career portal for future openings:
        <a href="{{ $career_url }}" target="_blank">{{ $career_url }}</a>
        </p>
       
    </div>
</body>

  
</html>
