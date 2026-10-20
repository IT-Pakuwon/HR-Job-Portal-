{{-- $company here is the candidate's company, so pin the layout footer to the group name --}}
@extends('emails.layouts.career', ['company' => 'Pakuwon Group'])

@section('title', 'Candidate Has Joined')
@section('preheader', $candidate_name . ' has been approved to join for PRF ' . $docid . '.')
@section('icon', '🎉')
@section('header', 'Candidate Has Joined')
@section('subtitle', $docid . ' — ' . $job_title)

@section('content')
    <p style="margin:0 0 12px;">Dear <strong>{{ $name }}</strong>,</p>

    <p style="margin:0 0 4px;">The candidate for the position you requested via PRF <strong style="color:#4338ca;">{{ $docid }}</strong> has been approved to <strong>Join</strong>:</p>

    @include('emails.partials.schedule-card', ['rows' => [
        ['icon' => '👤', 'label' => 'Candidate', 'value' => $candidate_name, 'sub' => $job_title],
        ['icon' => '🗓️', 'label' => 'Schedule Start', 'value' => $schedule_start],
        ['icon' => '🏢', 'label' => 'Company', 'value' => $company, 'sub' => $division],
    ]])

    @include('emails.partials.button', ['url' => $url, 'label' => 'View PRF  →'])

    <p style="margin:26px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
