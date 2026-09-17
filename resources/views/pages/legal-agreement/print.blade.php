<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AGREEMENT-{{ $agreement->agreement_id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #0f172a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        td { padding: 6px 8px; vertical-align: top; }
        .label { width: 160px; color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .section-title { margin-top: 20px; font-size: 13px; font-weight: bold; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; }
    </style>
</head>
<body>
    <h1>Legal Agreement</h1>
    <p style="color:#64748b;">{{ $agreement->agreement_id }}</p>

    <table>
        <tr><td class="label">Company</td><td>{{ $agreement->cpny_id }}</td></tr>
        <tr><td class="label">Agreement Date</td><td>{{ optional($agreement->agreement_date)->format('d M Y') }}</td></tr>
        <tr><td class="label">Business Name</td><td>{{ $agreement->business_name }}</td></tr>
        <tr><td class="label">Trade Name</td><td>{{ $agreement->trade_name }}</td></tr>
        <tr><td class="label">Tenant No</td><td>{{ $agreement->tenant_no }}</td></tr>
        <tr><td class="label">Floor / Unit</td><td>{{ $agreement->floor_id }} / {{ $agreement->unit_id }}</td></tr>
        <tr><td class="label">Business Address</td><td>{{ $agreement->business_address }}</td></tr>
        <tr><td class="label">PIC Penyewa</td><td>{{ $agreement->pic_penyewa }} ({{ $agreement->pic_phonenumber_penyewa }} / {{ $agreement->pic_email_penyewa }})</td></tr>
        <tr><td class="label">PIC Legal</td><td>{{ $agreement->pic_legal }}</td></tr>
        <tr><td class="label">PIC Leasing</td><td>{{ $agreement->pic_leasing }}</td></tr>
        <tr><td class="label">No. PSM/Addendum</td><td>{{ $agreement->no_psm_or_addendum }}</td></tr>
        <tr><td class="label">PSM/Addendum Date</td><td>{{ optional($agreement->psm_or_addendum_date)->format('d M Y') }}</td></tr>
        <tr><td class="label">Step</td><td>{{ $agreement->agreement_step_id }}</td></tr>
        <tr><td class="label">Status</td><td>{{ $agreement->status }}</td></tr>
        <tr><td class="label">Created By</td><td>{{ $agreement->created_user }}</td></tr>
    </table>

    @if (!empty($attachments))
        <p class="section-title">Attachments</p>
        <ul>
            @foreach ($attachments as $att)
                <li>{{ $att['display_name'] ?? $att['name'] ?? '-' }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
