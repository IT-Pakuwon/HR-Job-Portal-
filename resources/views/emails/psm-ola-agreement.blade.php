@extends('emails.layouts.master')

@section('title', $label.' '.ucfirst($event))

@section('icon', ['created' => '📝', 'updated' => '✏️', 'completed' => '✅', 'cancelled' => '🚫', 'reopened' => '🔓'][$event] ?? '✏️')

@section('header', $event === 'created' ? 'New '.$label.' Created' : $label.' '.ucfirst($event))

@section('subtitle')
{{ $actor }} {{ $verb }} this agreement. You are receiving this as the creator or PIC Legal.
@endsection

@section('content')

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.1em;text-transform:uppercase;">Agreement ID</p>
            <p style="margin:0;font-size:20px;font-weight:800;color:#1e40af;letter-spacing:0.04em;font-family:Courier New,Courier,monospace;">{{ $agreement->agreement_id }}</p>
        </td>
    </tr>
</table>

@php
    $rows = [
        'Tenant' => $agreement->business_name,
        'Trade Name' => $agreement->trade_name,
        'Company' => $agreement->cpny_id,
        'Unit' => $agreement->unit_id,
        $label === 'Addendum' ? 'No. Addendum' : 'No. PSM / Addendum' => $agreement->no_psm_or_addendum,
        'PIC Legal' => $picLegalNames,
        'PIC Leasing' => \App\Models\TrAgreement::picDisplayNames($agreement->picLeasingList()),
        'Created By' => \App\Models\TrAgreement::picDisplayNames([$agreement->created_user]) ?: $agreement->created_user,
    ];
@endphp

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    @foreach ($rows as $label => $value)
        <tr>
            <td width="150" style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">{{ $label }}</td>
            <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">{{ filled($value) ? $value : '-' }}</td>
        </tr>
    @endforeach
</table>

@if ($note)
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:20px;">
    <tr>
        <td style="background:#fef2f2;border:1.5px solid #fecaca;border-radius:12px;padding:14px 18px;font-size:13px;color:#7f1d1d;">
            <strong>Reason:</strong> {{ $note }}
        </td>
    </tr>
</table>
@endif

@if ($docUrl)
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
    <tr>
        <td align="center">
            <a href="{{ $docUrl }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-size:13px;font-weight:700;padding:12px 28px;border-radius:10px;">Open Agreement</a>
        </td>
    </tr>
</table>
@endif

@endsection
