@php
    $statusLabel = match ($agreement->status) {
        'P' => 'Open',
        'C' => 'Completed',
        'X' => 'Cancelled',
        default => $agreement->status,
    };
@endphp

{{-- Agreement ID highlight box --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.1em;text-transform:uppercase;">Agreement ID</p>
            <p style="margin:0;font-size:20px;font-weight:800;color:#1e40af;letter-spacing:0.04em;font-family:Courier New,Courier,monospace;">{{ $agreement->agreement_id }}</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">

    <tr>
        <td width="130" style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Requested By
        </td>
        <td style="padding:11px 0;font-size:13px;font-weight:700;color:#1e293b;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->created_user }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Company
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->cpny_id }}
        </td>
    </tr>

    <tr>
        <td style="padding:13px 16px 13px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Business / Tenant
        </td>
        <td style="padding:13px 0;font-size:13px;font-weight:700;color:#1e293b;line-height:1.6;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->business_name }}
            @if($agreement->trade_name)
                <br><span style="font-weight:400;color:#64748b;">{{ $agreement->trade_name }}</span>
            @endif
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Tenant No
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->tenant_no ?: '-' }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            PIC Legal
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->pic_legal ?: '-' }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            PIC Leasing
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $agreement->pic_leasing ?: '-' }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Step
        </td>
        <td style="padding:11px 0;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#fef9c3;border:1.5px solid #fde047;color:#854d0e;font-size:11px;font-weight:700;letter-spacing:0.04em;">
                {{ $agreement->agreement_step_id }}
            </span>
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;">
            Status
        </td>
        <td style="padding:11px 0;vertical-align:middle;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#dbeafe;border:1.5px solid #93c5fd;color:#1d4ed8;font-size:11px;font-weight:700;letter-spacing:0.04em;">
                {{ $statusLabel }}
            </span>
        </td>
    </tr>

</table>

@if (!empty($docUrl))
{{-- Open document button --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:28px;">
    <tr>
        <td align="center">
            <a href="{{ $docUrl }}" target="_blank"
                style="display:inline-block;background:#1d4ed8;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:8px;font-size:13px;font-weight:700;letter-spacing:0.02em;">
                Open Agreement &rarr;
            </a>
        </td>
    </tr>
</table>
@endif
