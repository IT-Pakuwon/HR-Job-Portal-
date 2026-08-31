@extends('emails.layouts.career')

@section('title', 'Your Application Has Moved Forward')

@section('icon', '🔄')

@section('header', 'Your Application Has Moved Forward')

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

<p style="margin:0 0 8px;font-size:13px;color:#475569;line-height:1.75;">
    You are being considered for the position of:
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
    <tr>
        <td style="background:#eef2ff;border:1.5px solid #c7d2fe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#4f46e5;letter-spacing:0.1em;text-transform:uppercase;">Position</p>
            <p style="margin:0;font-size:16px;font-weight:800;color:#3730a3;">{{ $job_title }}</p>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;">
    <tr>
        <td style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:12px;padding:14px 20px;">
            <p style="margin:0;font-size:13px;font-weight:700;color:#166534;line-height:1.6;">
                As you have already completed the application form, there is no need to fill it in again. Our recruitment team will continue the process and contact you regarding the next steps.
            </p>
        </td>
    </tr>
</table>

<p style="margin:0 0 28px;font-size:13px;color:#475569;line-height:1.75;">
    If you have any questions, feel free to reach out to us. Thank you for the time and interest you have shown in Pakuwon Group.
</p>

<p style="margin:0;font-size:13px;color:#475569;line-height:1.75;">
    Warm regards,<br>
    <strong style="color:#1e293b;">Talent Acquisition Pakuwon Group Jakarta</strong>
</p>

@endsection
