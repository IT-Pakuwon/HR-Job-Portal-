@extends('emails.layouts.career')

@section('title', 'Application Update')
@section('preheader', 'We found another position that may match your profile.')
@section('icon', '🔄')
@section('header', 'Your Application Has Been Updated')
@section('subtitle', 'A new position match at Pakuwon Group')

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $name }},</p>

    <p style="margin:0 0 12px;">Thank you for your enthusiasm and interest in joining <strong>Pakuwon Group</strong>.</p>

    <p style="margin:0 0 12px;">Based on our review of your CV, your qualifications are not currently aligned with the position you originally applied for. However, we've identified a potential match with another position currently available within Pakuwon Group:</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:16px 0;">
        <tr>
            <td style="background:#f0f4ff;border-left:4px solid #4338ca;border-radius:6px;padding:14px 16px;">
                <p style="margin:0;font-size:15px;font-weight:800;color:#4338ca;">{{ $job_title }}</p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 4px;">Since you've already completed the application form, you don't need to fill it in again — our recruitment team will continue the process and contact you about next steps.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
