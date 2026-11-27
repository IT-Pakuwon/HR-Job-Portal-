@extends('emails.layouts.career')

@section('title', 'Application Position Update')
@section('preheader', 'Your application has been moved to a new position.')
@section('icon', '🔄')
@section('header', 'Your Applied Position Has Changed')
@section('subtitle', 'An update on your application at Pakuwon Group')

@section('content')
    <p style="margin:0 0 12px;">Dear {{ $name }},</p>

    <p style="margin:0 0 12px;">Thank you for your enthusiasm and interest in joining <strong>Pakuwon Group</strong>.</p>

    <p style="margin:0 0 12px;">We would like to inform you that your application has been updated to a new position within Pakuwon Group:</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:16px 0;">
        <tr>
            <td style="background:#f0f4ff;border-left:4px solid #1c71ae;border-radius:6px;padding:14px 16px;">
                <p style="margin:0;font-size:15px;font-weight:800;color:#1c71ae;">{{ $job_title }}</p>
                @if ($division || $department)
                    <p style="margin:4px 0 0;font-size:12.5px;color:#64748b;">{{ implode(' · ', array_filter([$division, $department])) }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:0;">No action is needed from you at this stage. Our recruitment team will review your application for this position and contact you by email about the next steps.</p>

    <p style="margin:22px 0 0;">Warm regards,<br><strong>Talent Acquisition Pakuwon Group</strong></p>
@endsection
