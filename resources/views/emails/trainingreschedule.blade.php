@extends('emails.layouts.master')

@section('title', 'Training Schedule Changed')

@section('icon', '🔄')

@section('header', 'Training Schedule Changed')

@section('subtitle')
Your seat/waiting list slot is kept for the new date.
@endsection

@section('content')

{{-- Registration ID highlight box --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#b45309;letter-spacing:0.1em;text-transform:uppercase;">Registration ID</p>
            <p style="margin:0;font-size:20px;font-weight:800;color:#92400e;letter-spacing:0.04em;font-family:Courier New,Courier,monospace;">{{ $docid }}</p>
        </td>
    </tr>
</table>

<p style="margin:0 0 24px;font-size:14px;color:#334155;line-height:1.6;">
    Hello <strong>{{ $name }}</strong>, the schedule for the training <strong>{{ $training_name }}</strong> you are enrolled in has changed.
</p>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">

    <tr>
        <td width="130" style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Training
        </td>
        <td style="padding:11px 0;font-size:13px;font-weight:700;color:#1e293b;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $training_name }}
        </td>
    </tr>

    @if ($old_date)
    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Old Date
        </td>
        <td style="padding:11px 0;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b;font-size:12px;font-weight:700;letter-spacing:0.02em;text-decoration:line-through;">
                {{ \Carbon\Carbon::parse($old_date)->format('d M Y') }}
            </span>
        </td>
    </tr>
    @endif

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            New Date
        </td>
        <td style="padding:11px 0;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#dcfce7;border:1.5px solid #86efac;color:#166534;font-size:12px;font-weight:800;letter-spacing:0.02em;">
                {{ \Carbon\Carbon::parse($new_date)->format('d M Y') }}
            </span>
        </td>
    </tr>

    <tr>
        <td style="padding:13px 16px 13px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;">
            Reason
        </td>
        <td style="padding:13px 0;font-size:13px;color:#475569;line-height:1.6;vertical-align:top;">
            {{ $reason }}
        </td>
    </tr>

</table>

{{-- Cancel notice --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;">
    <tr>
        <td style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:10px;padding:14px 18px;">
            <p style="margin:0;font-size:12.5px;color:#92400e;line-height:1.6;">
                If you are unable to attend on the new date, please cancel your registration using the button below so the seat can be allocated to another participant.
            </p>
        </td>
    </tr>
</table>

{{-- CTA button --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
    <tr>
        <td align="center">
            <a href="{{ $url }}" target="_blank"
                style="display:inline-block;background:#1d4ed8;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:0.02em;">
                View My Registration &rarr;
            </a>
        </td>
    </tr>
</table>

@endsection
