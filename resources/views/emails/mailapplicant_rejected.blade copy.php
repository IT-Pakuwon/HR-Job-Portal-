@php
    $name = $name ?? 'Candidate';
    $job = $job_title ?? 'your application';
    $company = $company ?? 'Pakuwon Group Jakarta';
@endphp

<p>Dear <strong>{{ $name }}</strong>,</p>
<br>
<p>Thank you for your interest in <strong>{{ $company }}</strong> and making your time available for our recruitment process. </p>
<p>We are very grateful that we have so many interested candidates and that means we have to make some tough</p>
<p>choices from a large number of applicants. You have come this far, and we think it’s already an achievement!</p>
<p><strong>Unfortunately, we could not proceed with your application for the next step.</strong></p>
<br>
<p>But don't worry, we'll keep your profile and resume in our database and consider it for future opportunities.</p>
<br>
<p><strong>Wishing you for best endeavors in future !!</strong></p>
<br>
<p><strong>Warm regards,</strong><p>
<p><strong>Talent Acquisition {{ $company }}</strong><p>

<hr>

<p style="font-size:12px;color:#666;">
You can visit our career portal for future openings:
<a href="{{ $career_url }}" target="_blank">{{ $career_url }}</a>
</p>
