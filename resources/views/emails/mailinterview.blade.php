@extends('emails.layouts.career')

@section('title', 'Interview Invitation')
@section('preheader', 'You are invited to the next interview stage.')
@section('icon', '🗓️')
@section('header', "You're Invited to Interview")
@section('subtitle', 'User Interview & Psychotest' . (isset($jobtitle) && $jobtitle ? ' — ' . $jobtitle : ''))

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $name }},</p>

    <p style="margin:0 0 12px;">Congratulations! We're pleased to invite you to continue to the next stage of our recruitment process: <strong>User Interview & Psychotest</strong>.</p>

    <p style="margin:0 0 4px;">Here are the schedule details:</p>

    @include('emails.partials.detail-table', ['rows' => [
        ['label' => 'Day & Date', 'value' => $interview_date],
        ['label' => 'Time', 'value' => $starttime . ' – ' . $endtime . ' WIB'],
        ['label' => 'Venue', 'value' => $location],
        ['label' => 'Address', 'value' => $address],
        ['label' => 'PIC Recruitment', 'value' => 'Adela / Frengky'],
    ]])

    <p style="margin:0 0 12px;">Please bring your own stationery (pen & pencil) and wear formal attire.</p>

    <p style="margin:0 0 4px;">Kindly confirm your attendance by replying <strong>Attend / Not Attend / Reschedule</strong> via WhatsApp to <strong>+62 858 9001 4129</strong>. If you have any questions, feel free to reach out via email.</p>

    <p style="margin:22px 0 0;">Thank you, and we look forward to meeting you.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
