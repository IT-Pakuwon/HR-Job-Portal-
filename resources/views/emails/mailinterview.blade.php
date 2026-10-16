@extends('emails.layouts.career')

@section('title', 'Interview Invitation')
@section('preheader', 'You are invited to the next interview stage.')
@section('icon', '🗓️')
@section('header', "You're Invited to Interview")
@section('subtitle', ($schedule_type ?? 'User Interview & Psychotest') . (isset($jobtitle) && $jobtitle ? ' — ' . $jobtitle : ''))

@section('content')
    <p style="margin:0 0 12px;">Dear <strong>{{ $name }}</strong>,</p>

    <p style="margin:0 0 4px;">Congratulations! We're pleased to invite you to continue to the next stage of our recruitment process: <strong style="color:#4338ca;">{{ $schedule_type ?? 'User Interview & Psychotest' }}</strong>. Here are your schedule details:</p>

    @include('emails.partials.schedule-card', ['rows' => [
        ['icon' => '🗓️', 'label' => 'Day & Date', 'value' => $interview_date],
        ['icon' => '⏰', 'label' => 'Time', 'value' => $starttime . ' – ' . $endtime . ' WIB'],
        ['icon' => '📍', 'label' => 'Venue', 'value' => $location, 'sub' => $address],
        ['icon' => '👤', 'label' => 'PIC Recruitment', 'value' => $pic_recruitment ?? 'Adela / Frengky'],
    ]])

    {{-- What to prepare --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 22px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;border-collapse:separate;">
        <tr>
            <td style="padding:14px 18px;">
                <div style="margin:0 0 6px;font-size:12.5px;font-weight:800;color:#92400e;">📝 Please prepare</div>
                <div style="font-size:13.5px;color:#78350f;line-height:1.7;">
                    •&nbsp; Bring your own stationery (pen &amp; pencil)<br>
                    •&nbsp; Wear formal attire
                </div>
            </td>
        </tr>
    </table>

    {{-- Confirm attendance --}}
    <p style="margin:0 0 10px;">Kindly confirm your attendance by replying with one of the following:</p>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 6px;">
        <tr>
            <td style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:700;color:#047857;">✔ Attend</td>
            <td width="8"></td>
            <td style="background:#fef2f2;border:1px solid #fecaca;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:700;color:#b91c1c;">✖ Not Attend</td>
            <td width="8"></td>
            <td style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:5px 14px;font-size:12.5px;font-weight:700;color:#1d4ed8;">↻ Reschedule</td>
        </tr>
    </table>

    @if(($group_cpny_id ?? null) === 'SBY')
        @include('emails.partials.button', [
            'url' => 'mailto:hrd@pakuwon.com?subject=' . rawurlencode('Interview Confirmation - ' . $name),
            'label' => '✉️  Reply via Email',
        ])
        <p style="margin:8px 0 0;font-size:12.5px;color:#64748b;">Send your reply to <strong style="color:#334155;">hrd@pakuwon.com</strong>. If you have any questions, feel free to reach out via email.</p>
    @else
        @include('emails.partials.button', [
            'url' => 'https://wa.me/6285890014129?text=' . rawurlencode('Interview Confirmation - ' . $name . ': '),
            'label' => '💬  Reply via WhatsApp',
        ])
        <p style="margin:8px 0 0;font-size:12.5px;color:#64748b;">Send your reply via WhatsApp to <strong style="color:#334155;">+62 858 9001 4129</strong>. If you have any questions, feel free to reach out via email.</p>
    @endif

    <p style="margin:26px 0 0;">Thank you, and we look forward to meeting you.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
