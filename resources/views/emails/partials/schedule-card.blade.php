{{-- $rows: array of ['icon' => ..., 'label' => ..., 'value' => ..., 'sub' => optional second line] --}}
@php
    $rows = array_values(array_filter($rows, fn ($row) => filled($row['value'] ?? null)));
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0;background:#f5f7ff;border:1px solid #e0e7ff;border-left:4px solid #4338ca;border-radius:10px;border-collapse:separate;">
    <tr>
        <td style="padding:4px 20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                @foreach ($rows as $i => $row)
                    <tr>
                        <td width="36" valign="top" style="padding:14px 0;{{ $i > 0 ? 'border-top:1px solid #e0e7ff;' : '' }}font-size:18px;line-height:1.3;">
                            {{ $row['icon'] ?? '' }}
                        </td>
                        <td valign="top" style="padding:14px 0;{{ $i > 0 ? 'border-top:1px solid #e0e7ff;' : '' }}">
                            <div style="font-size:10.5px;font-weight:700;letter-spacing:0.09em;text-transform:uppercase;color:#6366f1;">{{ $row['label'] }}</div>
                            <div style="margin-top:3px;font-size:15px;font-weight:700;color:#1e293b;line-height:1.4;">{{ $row['value'] }}</div>
                            @if (filled($row['sub'] ?? null))
                                <div style="margin-top:2px;font-size:12.5px;font-weight:400;color:#64748b;line-height:1.5;">{{ $row['sub'] }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>
