@extends('emails.layouts.career')

@php
    $name = $name ?? 'Candidate';
    $job = $job_title ?? 'your application';
    $company = $company ?? 'Pakuwon Group';
@endphp

@section('title', 'Application Update')
@section('preheader', 'An update on your application at ' . $company . '.')
@section('icon', '📋')
@section('header', 'Application Update')
@section('subtitle', $company)

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $name }},</p>

    <p style="margin:0 0 12px;">Thank you for your interest in <strong>{{ $company }}</strong> and for making your time available for our recruitment process.</p>

    <p style="margin:0 0 12px;">We're grateful for the many interested candidates we received, which means we have to make some tough choices from a large number of applicants. You've come this far, and we think that's already an achievement.</p>

    <p style="margin:0 0 12px;"><strong>Unfortunately, we could not proceed with your application for the next step.</strong></p>

    <p style="margin:0 0 12px;">But don't worry — we'll keep your profile and resume in our database and consider it for future opportunities.</p>

    <p style="margin:0 0 4px;"><strong>Wishing you the best in your future endeavors!</strong></p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition {{ $company }}</strong></p>

    <hr style="border:none;border-top:1px solid #e2e8f0;margin:22px 0 14px;">
    <p style="margin:0;font-size:12px;color:#64748b;">
        You can visit our career portal for future openings:
        <a href="{{ $career_url }}" target="_blank" style="color:#4338ca;">{{ $career_url }}</a>
    </p>
@endsection
