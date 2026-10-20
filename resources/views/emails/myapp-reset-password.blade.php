@extends('emails.layouts.system')

@section('title', 'Reset your password')

@section('preheader', 'Use this link to choose a new My APP password.')

@section('icon', '🔑')

@section('header', 'Reset your password')

@section('subtitle')
Hi {{ $user->name ?? $user->username }}, we got a request to reset your My APP password.
@endsection

@section('content')
<p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#334155;">
    Tap the button below to choose a new password. The link works once and expires in 60 minutes.
</p>
<p style="margin:0 0 22px;">
    <a href="{{ $resetUrl }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 26px;border-radius:10px;">Reset password</a>
</p>
<p style="margin:0 0 6px;font-size:12px;line-height:1.6;color:#64748b;">Username: <strong>{{ $user->username }}</strong></p>
<p style="margin:0;font-size:12px;line-height:1.6;color:#94a3b8;">If you didn't ask for this, you can safely ignore this email &mdash; your password stays the same.</p>
@endsection
