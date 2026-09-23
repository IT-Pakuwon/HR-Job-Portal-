@extends('emails.layouts.career')

@section('title', 'New Position Match')
@section('preheader', 'We found another position for you — please complete the follow-up form.')
@section('icon', '🔄')
@section('header', 'New Position Match Found')
@section('subtitle', 'Action required: complete your form')

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

    <p style="margin:0 0 4px;">If you're interested in being considered for this position, please complete the follow-up form below as the next step in our recruitment process.</p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Complete Your Application'])

    <p style="margin:22px 0 0;">Thank you for the time and interest you've shown in Pakuwon Group. We look forward to continuing the process with you.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
