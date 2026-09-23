{{-- Event ID highlight box --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.1em;text-transform:uppercase;">Event ID</p>
            <p style="margin:0;font-size:20px;font-weight:800;color:#1e40af;letter-spacing:0.04em;font-family:Courier New,Courier,monospace;">{{ $event->event_id }}</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">

    <tr>
        <td width="130" style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Event Name
        </td>
        <td style="padding:11px 0;font-size:13px;font-weight:700;color:#1e293b;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $event->event_name }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Company
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $companyName ?? $event->cpnyid }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Location
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $locationName ?? $event->event_location_id }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Event Dates
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ optional($event->event_start_date)->format('d-M-Y') }} &rarr; {{ optional($event->event_end_date)->format('d-M-Y') }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            PIC (Internal)
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $event->pic_event ?: '-' }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;">
            Status
        </td>
        <td style="padding:11px 0;vertical-align:middle;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#e5eeff;border:1.5px solid #93c5fd;color:#2149b3;font-size:11px;font-weight:700;letter-spacing:0.04em;">
                {{ $event->event_status }}
            </span>
        </td>
    </tr>

</table>
