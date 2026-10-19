@extends('emails.layouts.career')

@section('title', 'Employment Start Schedule')
@section('preheader', 'Your official start date and onboarding details.')
@section('icon', '🎉')
@section('header', 'Welcome to the Team')
@section('subtitle', 'Your employment start schedule')

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $full_name }},</p>

    <p style="margin:0 0 4px;">We're pleased to share your employment schedule details:</p>

    @include('emails.partials.schedule-card', ['rows' => [
        ['icon' => '🗓️', 'label' => 'Official Start Date', 'value' => $work_start_date],
    ]])

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 18px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;border-collapse:separate;">
        <tr>
            <td style="padding:14px 18px;">
                <div style="margin:0 0 6px;font-size:12.5px;font-weight:800;color:#92400e;">📝 On your first day</div>
                <div style="font-size:13.5px;color:#78350f;line-height:1.7;">
                    •&nbsp; Bring a valid ID<br>
                    •&nbsp; Bring any onboarding documents requested by HR
                </div>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px;">Please ensure you're available on the date above. If you have questions or need to propose an adjustment, feel free to reply to this email.</p>

    <p style="margin:0 0 4px;">We look forward to welcoming you on your first day.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
