@extends('emails.layouts.master')

@section('title', 'PSM/Addendum Delivered')

@section('icon', '📤')

@section('header', 'PSM / Addendum Delivered')

@section('subtitle')
The hardcopy PSM/Addendum has been sent to the tenant.
@endsection

@section('content')

{{-- Delivery confirmation --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:26px;">
    <tr>
        <td style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:16px 18px;">
            <p style="margin:0 0 8px;display:inline-block;background:#dcfce7;border-radius:999px;padding:2px 10px;font-size:10.5px;font-weight:700;color:#166534;letter-spacing:0.04em;text-transform:uppercase;">
                &#10003; Delivery Confirmed
            </p>
            <p style="margin:0;font-size:13.5px;line-height:1.65;color:#14532d;">
                PSM/Addendum No. <strong style="color:#166534;">{{ $agreement->no_psm_or_addendum }}</strong>
                for <strong style="color:#166534;">{{ $agreement->business_name }}</strong>
                was sent on <strong style="color:#166534;">{{ $agreement->psm_or_addendum_delivery_date ? \Carbon\Carbon::parse($agreement->psm_or_addendum_delivery_date)->format('d M Y') : '-' }}</strong>
                to <strong style="color:#166534;">{{ $agreement->business_address ?: $agreement->business_name }}</strong>.
            </p>
        </td>
    </tr>
</table>

@include('emails.partials.agreement-detail')

@endsection
