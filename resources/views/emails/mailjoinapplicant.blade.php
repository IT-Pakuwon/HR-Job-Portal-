@extends('emails.layouts.career')

@section('title', 'Employment Start Schedule')
@section('preheader', 'Your official start date and onboarding details.')
@section('icon', '🎉')
@section('header', 'Welcome to the Team')
@section('subtitle', 'Your employment start schedule')

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $full_name }},</p>

    <p style="margin:0 0 4px;">We're pleased to share your employment schedule details:</p>

    @include('emails.partials.detail-table', ['rows' => [
        ['label' => 'Official Start Date', 'value' => $work_start_date],
    ]])

    <p style="margin:0 0 12px;">Please ensure you're available on the date above. On your first day, kindly bring a valid ID and any onboarding documents requested by HR. If you have questions or need to propose an adjustment, feel free to reply to this email.</p>

    <p style="margin:0 0 4px;">We look forward to welcoming you on your first day.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
