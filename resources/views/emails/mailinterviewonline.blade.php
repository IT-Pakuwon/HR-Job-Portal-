@extends('emails.layouts.career')

@section('title', $title)
@section('preheader', 'Online interview invitation')
@section('header', $title)
@section('subtitle', 'Online Interview')

@section('content')
    <p style="margin:0 0 12px;">Dear <strong>{{ $hod ?? $name ?? 'Participant' }}</strong>,</p>
    @if(!empty($full_name))
        <p>Candidate: <strong>{{ $full_name }}</strong> ({{ $job_title }})</p>
    @endif
    <p style="white-space:pre-line;margin:0 0 20px;">{{ $description }}</p>
    @include('emails.partials.schedule-card', ['rows' => [
        ['label' => 'Date', 'value' => $interview_date],
        ['label' => 'Time', 'value' => $starttime . ' – ' . $endtime . ' WIB'],
    ]])
    @php
        // Accept either a single URL or a pasted invitation with meeting ID/passcode.
        preg_match('~https?://[^\s<>"\x27]+~i', $link_onlinemeeting ?? '', $meetingLinks);
        $meetingUrl = isset($meetingLinks[0]) ? rtrim($meetingLinks[0], '.,;)') : null;
        $meetingUrl = $meetingUrl && filter_var($meetingUrl, FILTER_VALIDATE_URL) ? $meetingUrl : null;
        $meetingDetailParts = preg_split('~(https?://[^\s<>"\x27]+)~i', $link_onlinemeeting ?? '', -1, PREG_SPLIT_DELIM_CAPTURE);
    @endphp
    @if($meetingUrl)
        @include('emails.partials.button', ['url' => $meetingUrl, 'label' => 'Join Online Meeting'])
    @endif
    <p style="margin:16px 0 6px;font-weight:700;">Online Meeting Details</p>
    <div style="overflow-wrap:anywhere;word-break:break-word;">@foreach($meetingDetailParts as $part)@php
        $partUrl = rtrim($part, '.,;)');
        $isMeetingLink = preg_match('~^https?://~i', $partUrl) && filter_var($partUrl, FILTER_VALIDATE_URL);
    @endphp@if($isMeetingLink)<a href="{{ $partUrl }}" target="_blank" rel="noopener noreferrer" style="color:#4338ca;text-decoration:underline;">{{ $partUrl }}</a>{{ substr($part, strlen($partUrl)) }}@else{!! nl2br(e($part)) !!}@endif@endforeach</div>
    <p style="margin:26px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
