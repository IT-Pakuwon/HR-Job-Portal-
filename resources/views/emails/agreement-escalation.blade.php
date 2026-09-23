@extends('emails.layouts.master')

@section('title', 'Legal Agreement Escalated')

@section('icon', '🚨')

@section('header', 'Escalated to Marketing/Leasing')

@section('subtitle')
Automatically escalated after two reminder letters went unanswered.
@endsection

@section('content')

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
    <tr>
        <td style="background:#fef2f2;border:1.5px solid #fca5a5;border-radius:12px;padding:14px 18px;font-size:13px;line-height:1.65;color:#7f1d1d;">
            The tenant has not returned the signed PSM/Addendum hardcopy despite <strong>Surat 1</strong> and
            <strong>Surat 2</strong> reminder letters (attached copy of Surat 2). Legal has exhausted the reminder
            process — this has been automatically moved to <strong>ESCALATED</strong> and now needs Marketing/Leasing
            to follow up directly with the tenant.
        </td>
    </tr>
</table>

@include('emails.partials.agreement-detail')

@endsection
