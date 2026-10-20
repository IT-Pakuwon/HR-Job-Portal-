@extends('emails.layouts.master')

@section('title', 'Legal Agreement On Hold')

@section('icon', '⏸️')

@section('header', 'Legal Agreement On Hold')

@section('subtitle')
This legal agreement has been put on hold.
@endsection

@section('content')

@if (!empty($reason))
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td style="background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;padding:14px 18px;font-size:13px;line-height:1.65;color:#78350f;">
            <strong>Reason:</strong> {{ $reason }}
        </td>
    </tr>
</table>
@endif

@include('emails.partials.agreement-detail')

@endsection
