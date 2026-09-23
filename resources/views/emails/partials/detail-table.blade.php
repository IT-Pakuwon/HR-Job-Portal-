{{-- $rows: array of ['label' => ..., 'value' => ...] --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e2e8f0;border-radius:8px;border-collapse:separate;overflow:hidden;margin:18px 0;">
    @foreach ($rows as $i => $row)
        <tr>
            <td style="padding:10px 14px;background:#f8fafc;font-size:12.5px;color:#64748b;font-weight:700;width:38%;{{ $i > 0 ? 'border-top:1px solid #e2e8f0;' : '' }}">
                {{ $row['label'] }}
            </td>
            <td style="padding:10px 14px;font-size:13.5px;color:#1e293b;{{ $i > 0 ? 'border-top:1px solid #e2e8f0;' : '' }}">
                {{ $row['value'] }}
            </td>
        </tr>
    @endforeach
</table>
