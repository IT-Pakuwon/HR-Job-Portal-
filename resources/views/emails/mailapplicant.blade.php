@extends('emails.layouts.career')

@section('title', 'Complete Your Application')
@section('preheader', 'Please fill out your application form to continue.')
@section('icon', '📝')
@section('header', 'Complete Your Application')
@section('subtitle', 'One more step to join Pakuwon Group')

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $name }},</p>

    <p style="margin:0 0 12px;">Thank you for your interest in <strong>Pakuwon Group</strong>.</p>
    <p style="margin:0 0 4px;">To continue in our recruitment process, please fill out your application form using the button below.</p>

    @include('emails.partials.button', ['url' => $url, 'label' => 'Continue Your Application'])

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
