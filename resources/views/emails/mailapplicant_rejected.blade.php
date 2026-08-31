@extends('emails.layouts.career')

@php
    $name = $name ?? 'Candidate';
    $job = $job_title ?? 'your application';
    $company = $company ?? 'Pakuwon Group Jakarta';
@endphp

@section('title', 'Application Update')

@section('icon', '📋')

@section('header', 'Application Update')

@section('subtitle')
Regarding your application to {{ $company }}.
@endsection

@section('content')

<p style="margin:0 0 16px;font-size:14px;color:#334155;line-height:1.7;">
    Dear <strong>{{ $name }}</strong>,
</p>

<p style="margin:0 0 16px;font-size:13px;color:#475569;line-height:1.75;">
    Thank you for your interest in <strong>{{ $company }}</strong> and for making your time available throughout our recruitment process.
</p>

<p style="margin:0 0 20px;font-size:13px;color:#475569;line-height:1.75;">
    We are very grateful for the number of interested candidates, which means we have to make some tough choices among a large number of applicants. Reaching this stage is already an achievement.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
    <tr>
        <td style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 20px;">
            <p style="margin:0;font-size:13px;font-weight:700;color:#991b1b;line-height:1.6;">
                Unfortunately, we are unable to proceed with your application for the next step.
            </p>
        </td>
    </tr>
</table>

<p style="margin:0 0 16px;font-size:13px;color:#475569;line-height:1.75;">
    We will keep your profile and resume in our database and will consider it for future opportunities that match your qualifications.
</p>

<p style="margin:0 0 28px;font-size:13px;color:#475569;line-height:1.75;">
    Wishing you the very best in your future endeavors.
</p>

<p style="margin:0 0 28px;font-size:13px;color:#475569;line-height:1.75;">
    Warm regards,<br>
    <strong style="color:#1e293b;">Talent Acquisition {{ $company }}</strong>
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #f1f5f9;padding-top:18px;">
    <tr>
        <td>
            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.7;">
                You can visit our career portal for future openings:<br>
                <a href="{{ $career_url }}" target="_blank" style="color:#4338ca;font-weight:700;text-decoration:none;">{{ $career_url }}</a>
            </p>
        </td>
    </tr>
</table>

@endsection
