@extends('emails.layouts.career')

@section('title', 'Candidate Interview Schedule')
@section('preheader', 'Interview schedule - ' . $full_name . ' (' . $job_title . ')')
@section('icon', '🗓️')
@section('header', 'Candidate Interview Schedule')
@section('subtitle', $full_name . ' — ' . $job_title)

@section('content')
    <p style="margin:0 0 12px;">Dear <strong>{{ $hod }}</strong>,</p>

    <p style="margin:0 0 4px;">We kindly invite you to attend the following <strong style="color:#4338ca;">Candidate Interview</strong> session:</p>

    @include('emails.partials.schedule-card', ['rows' => [
        ['icon' => '👤', 'label' => 'Candidate', 'value' => $full_name, 'sub' => $job_title],
        ['icon' => '🗓️', 'label' => 'Date', 'value' => $interview_date],
        ['icon' => '⏰', 'label' => 'Time', 'value' => $starttime . ' – ' . $endtime . ' WIB', 'sub' => 'Western Indonesia Time'],
        ['icon' => '📍', 'label' => 'Location', 'value' => $location, 'sub' => $location_address],
    ]])

    @include('emails.partials.button', ['url' => $url, 'label' => 'View Candidate  →'])

    <p style="margin:26px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
