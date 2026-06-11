{{-- Ticket ID highlight box --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:28px;">
    <tr>
        <td style="background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;padding:14px 20px;">
            <p style="margin:0 0 2px;font-size:10px;font-weight:700;color:#3b82f6;letter-spacing:0.1em;text-transform:uppercase;">Ticket ID</p>
            <p style="margin:0;font-size:20px;font-weight:800;color:#1e40af;letter-spacing:0.04em;font-family:Courier New,Courier,monospace;">{{ $ticket->ticketid }}</p>
        </td>
    </tr>
</table>

{{-- Detail rows --}}
<table width="100%" cellpadding="0" cellspacing="0" border="0">

    <tr>
        <td width="130" style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Requester
        </td>
        <td style="padding:11px 0;font-size:13px;font-weight:700;color:#1e293b;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $ticket->user_peminta }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Ticket Type
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $ticket->ticket_type }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Category
        </td>
        <td style="padding:11px 0;font-size:13px;color:#334155;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            {{ $ticket->category->ticket_category_name ?? $ticket->ticket_categoryid }}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            Priority
        </td>
        <td style="padding:11px 0;vertical-align:middle;border-bottom:1px solid #f1f5f9;">
            @php
                $p = strtolower($ticket->ticket_priority ?? '');
                if (str_contains($p, 'low')) {
                    $pBg = '#dcfce7'; $pBorder = '#86efac'; $pTxt = '#166534';
                } elseif (str_contains($p, 'critical') || str_contains($p, 'urgent')) {
                    $pBg = '#fdf4ff'; $pBorder = '#e879f9'; $pTxt = '#7e22ce';
                } elseif (str_contains($p, 'high')) {
                    $pBg = '#fee2e2'; $pBorder = '#fca5a5'; $pTxt = '#991b1b';
                } else {
                    $pBg = '#fef9c3'; $pBorder = '#fde047'; $pTxt = '#854d0e';
                }
            @endphp
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:{{ $pBg }};border:1.5px solid {{ $pBorder }};color:{{ $pTxt }};font-size:11px;font-weight:700;letter-spacing:0.04em;">
                {{ $ticket->ticket_priority }}
            </span>
        </td>
    </tr>

    <tr>
        <td style="padding:13px 16px 13px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Summary
        </td>
        <td style="padding:13px 0;font-size:13px;font-weight:700;color:#1e293b;line-height:1.6;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {{ $ticket->issue_summary }}
        </td>
    </tr>

    <tr>
        <td style="padding:13px 16px 13px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            Description
        </td>
        <td style="padding:13px 0;font-size:13px;color:#475569;line-height:1.75;vertical-align:top;border-bottom:1px solid #f1f5f9;">
            {!! nl2br(e(strip_tags($ticket->issue_descr))) !!}
        </td>
    </tr>

    <tr>
        <td style="padding:11px 16px 11px 0;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;vertical-align:middle;">
            Status
        </td>
        <td style="padding:11px 0;vertical-align:middle;">
            <span style="display:inline-block;padding:3px 14px;border-radius:999px;background:#dbeafe;border:1.5px solid #93c5fd;color:#1d4ed8;font-size:11px;font-weight:700;letter-spacing:0.04em;">
                {{ $ticket->status }}
            </span>
        </td>
    </tr>

</table>
