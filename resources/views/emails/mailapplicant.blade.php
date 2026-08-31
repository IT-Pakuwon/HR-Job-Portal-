@extends('emails.layouts.career')

@section('title', 'Complete Your Application')

@section('icon', '📝')

@section('header', 'Complete Your Application')

@section('subtitle')
One more step to join the recruitment process at Pakuwon Career.
@endsection

@section('content')

<p style="margin:0 0 16px;font-size:14px;color:#334155;line-height:1.7;">
    Dear <strong>{{ $name ?? 'Candidate' }}</strong>,
</p>

<p style="margin:0 0 16px;font-size:13px;color:#475569;line-height:1.75;">
    Thank you for your interest in joining <strong>Pakuwon Group Jakarta</strong>.
</p>

<p style="margin:0 0 28px;font-size:13px;color:#475569;line-height:1.75;">
    To continue your recruitment process, please complete your application form using the button below.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
            <a href="{{ $url }}" target="_blank"
                style="display:inline-block;background:#4338ca;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:0.02em;">
                Complete Your Application &rarr;
            </a>
        </td>
    </tr>
</table>

<p style="margin:32px 0 0;font-size:13px;color:#475569;line-height:1.75;">
    Warm regards,<br>
    <strong style="color:#1e293b;">Talent Acquisition Pakuwon Group Jakarta</strong>
</p>

@endsection
