@extends('emails.layouts.master')

@section('title', 'Documents Not Received')

@section('icon', '📎')

@section('header', 'Documents Not Received')

@section('subtitle')
Hi {{ $name }}, these agreements are still waiting for documents. You are receiving this as the creator or PIC Legal. This reminder repeats every 7 days until every document is received.
@endsection

@section('content')

@foreach ($items as $item)
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;border:1.5px solid #e2e8f0;border-radius:12px;">
    <tr>
        <td style="padding:14px 18px;background:#eff6ff;border-radius:12px 12px 0 0;">
            <p style="margin:0;font-size:16px;font-weight:800;color:#1e40af;font-family:Courier New,Courier,monospace;">{{ $item['agreement_id'] }}
                <span style="font-size:11px;font-weight:700;color:#64748b;font-family:Arial,sans-serif;">&nbsp;{{ $item['label'] }} · day {{ $item['days'] }}</span></p>
            <p style="margin:4px 0 0;font-size:13px;color:#334155;">{{ $item['tenant'] ?: '-' }}@if ($item['unit']) · {{ $item['unit'] }}@endif</p>
        </td>
    </tr>
    <tr>
        <td style="padding:12px 18px;">
            <p style="margin:0 0 6px;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;">Not received ({{ count($item['documents']) }})</p>
            @foreach ($item['documents'] as $doc)
                <p style="margin:0;padding:6px 0;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;">• {{ $doc }}</p>
            @endforeach
            <p style="margin:14px 0 0;"><a href="{{ $item['url'] }}" style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;font-size:12px;font-weight:700;padding:9px 20px;border-radius:8px;">Open Agreement</a></p>
        </td>
    </tr>
</table>
@endforeach

@endsection
