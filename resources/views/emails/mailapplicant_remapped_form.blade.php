@extends('emails.layouts.career')

@section('title', 'A New Opportunity For You')

@section('icon', '🔄')

@section('header', 'A New Opportunity For You')

@section('subtitle')
We found a better-matched role for your profile.
@endsection

@section('content')

<p style="margin:0 0 16px;font-size:14px;color:#334155;line-height:1.7;">
    Dear <strong>{{ $name ?? 'Candidate' }}</strong>,
</p>

<p style="margin:0 0 16px;font-size:13px;color:#475569;line-height:1.75;">
    Thank you for your enthusiasm and interest in joining <strong>Pakuwon Group</strong>.
</p>

<p style="margin:0 0 20px;font-size:13px;color:#475569;line-height:1.75;">
    Based on our review of your CV, your qualifications are not currently aligned with the position you originally applied for. However, we are pleased to share that we have identified a potential match with another position currently available within Pakuwon Group.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td style="background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#4f46e5;letter-spacing:0.1em;text-transform:uppercase;">Position</p>
            <p style="margin:0;font-size:16px;font-weight:800;color:#3730a3;">{{ $job_title }}</p>
        </td>
    </tr>
</table>

<p style="margin:0 0 28px;font-size:13px;color:#475569;line-height:1.75;">
    If you are interested in being considered for this position, please complete the follow-up form below as part of the next stage of our recruitment process.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <a href="{{ $url }}" target="_blank"
                style="display:inline-block;background:#4338ca;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:0.02em;">
                Complete Your Application &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin:32px 0 0;font-size:13px;color:#475569;line-height:1.75;">
    Thank you for the time and interest you have shown in Pakuwon Group. We look forward to continuing the recruitment process with you.
</p>

<p style="margin:20px 0 0;font-size:13px;color:#475569;line-height:1.75;">
    Warm regards,<br>
    <strong style="color:#1e293b;">Talent Acquisition Pakuwon Group Jakarta</strong>
</p>

@endsection
