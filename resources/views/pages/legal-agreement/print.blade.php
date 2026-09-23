<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>AGREEMENT-{{ $agreement->agreement_id }}</title>
    <style>
        @page { margin: 34px 42px; }

        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        table { border-collapse: collapse; }
        ul { margin: 0; padding-left: 16px; }

        /* Letterhead */
        .letterhead-table { width: 100%; border-bottom: 3px solid #0f172a; padding-bottom: 10px; margin-bottom: 18px; }
        .letterhead-table td { padding: 0; vertical-align: bottom; }
        .cpny-name { font-size: 17px; font-weight: bold; color: #0f172a; }
        .cpny-address { font-size: 9px; color: #64748b; margin-top: 3px; }
        .doc-label { font-size: 8px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
        .doc-id { font-size: 15px; font-weight: bold; color: #0f172a; margin-top: 2px; }

        /* Title row */
        .title-table { width: 100%; margin-bottom: 16px; }
        .title-table td { padding: 0; vertical-align: middle; }
        h1 { font-size: 15px; margin: 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-hold { background: #fef9c3; color: #a16207; }
        .badge-escalated { background: #fee2e2; color: #b91c1c; }
        .badge-completed { background: #f1f5f9; color: #334155; }

        /* Section titles */
        .section-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #0f172a; background: #f8fafc; border-left: 3px solid #0f172a; padding: 5px 9px; margin: 18px 0 8px; }

        /* Two-column info grid, built as a table so dompdf lays it out reliably */
        .info-grid { width: 100%; }
        .info-grid > tbody > tr > td { width: 50%; vertical-align: top; padding: 0; }
        .info-grid > tbody > tr > td.gap { width: 4%; }

        table.kv { width: 100%; border-collapse: collapse; }
        table.kv td { padding: 6px 2px; font-size: 10.5px; border-bottom: 1px solid #eef2f7; vertical-align: top; }
        table.kv td.label { width: 40%; color: #64748b; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: bold; }
        table.kv.full td.label { width: 22%; }

        /* Attachments */
        table.attachment-table { width: 100%; border-collapse: collapse; }
        table.attachment-table th { text-align: left; font-size: 8.5px; text-transform: uppercase; letter-spacing: 0.3px; color: #64748b; padding: 5px 6px; border-bottom: 1px solid #e2e8f0; }
        table.attachment-table td { font-size: 10.5px; padding: 6px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        table.attachment-table td.att-date { color: #94a3b8; font-size: 9.5px; width: 34%; }

        /* Signatures */
        .sign-table { width: 100%; margin-top: 36px; }
        .sign-table td { width: 33.33%; text-align: center; font-size: 10px; vertical-align: top; }
        .sign-role { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; padding-bottom: 6px; }
        .sign-line { border-top: 1px solid #94a3b8; padding-top: 4px; font-weight: bold; text-transform: uppercase; }
        .sign-hint { color: #94a3b8; font-size: 8.5px; margin-top: 2px; text-transform: uppercase; }

        /* Footer */
        .doc-footer { margin-top: 28px; padding-top: 8px; border-top: 1px solid #e2e8f0; color: #94a3b8; font-size: 8px; text-align: center; }
    </style>
</head>
<body>
    <table class="letterhead-table">
        <tr>
            <td style="text-align: left;">
                <div class="cpny-name">{{ $company->cpny_name ?? $agreement->cpny_id }}</div>
                @if ($company)
                    <div class="cpny-address">
                        {{ collect([$company->address_line1, $company->address_line2, $company->city])->filter()->implode(', ') }}
                        @if ($company->phone) &bull; {{ $company->phone }} @endif
                    </div>
                @endif
            </td>
            <td style="text-align: right;">
                <div class="doc-label">Agreement No.</div>
                <div class="doc-id">{{ $agreement->agreement_id }}</div>
            </td>
        </tr>
    </table>

    <table class="title-table">
        <tr>
            <td style="text-align: left;"><h1>Legal Agreement</h1></td>
            <td style="text-align: right;">
                <span class="badge badge-{{ strtolower($agreement->agreement_step_id) }}">{{ $agreement->agreement_step_id }}</span>
            </td>
        </tr>
    </table>

    <div class="section-title">Tenant Information</div>
    <table class="info-grid">
        <tr>
            <td>
                <table class="kv">
                    <tr><td class="label">Company</td><td>{{ $company->cpny_name ?? $agreement->cpny_id }}</td></tr>
                    <tr><td class="label">Business Name</td><td>{{ $agreement->business_name }}</td></tr>
                    <tr><td class="label">Tenant No</td><td>{{ $agreement->tenant_no }}</td></tr>
                </table>
            </td>
            <td class="gap"></td>
            <td>
                <table class="kv">
                    <tr><td class="label">Agreement Date</td><td>{{ optional($agreement->agreement_date)->format('d M Y') }}</td></tr>
                    <tr><td class="label">Trade Name</td><td>{{ $agreement->trade_name }}</td></tr>
                    <tr><td class="label">Floor / Unit</td><td>{{ $agreement->floor_id }} / {{ $agreement->unit_id }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="kv full">
        <tr><td class="label">Business Address</td><td>{{ $agreement->business_address }}</td></tr>
        <tr><td class="label">PIC Penyewa</td><td>{{ $agreement->pic_penyewa }} ({{ $agreement->pic_phonenumber_penyewa }} / {{ $agreement->pic_email_penyewa }})</td></tr>
    </table>

    <div class="section-title">Document &amp; PIC Information</div>
    <table class="info-grid">
        <tr>
            <td>
                <table class="kv">
                    <tr><td class="label">PIC Legal</td><td>{{ $picLegalNames ?: '-' }}</td></tr>
                    <tr><td class="label">No. PSM/Addendum</td><td>{{ $agreement->no_psm_or_addendum ?: '-' }}</td></tr>
                </table>
            </td>
            <td class="gap"></td>
            <td>
                <table class="kv">
                    <tr><td class="label">PIC Leasing</td><td>{{ $picLeasingNames ?: '-' }}</td></tr>
                    <tr><td class="label">PSM/Addendum Date</td><td>{{ optional($agreement->psm_or_addendum_date)->format('d M Y') ?: '-' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @if (!empty($attachments))
        <div class="section-title">Attachments</div>
        <table class="attachment-table">
            <tr>
                <th>File Name</th>
                <th>Uploaded</th>
            </tr>
            @foreach ($attachments as $att)
                <tr>
                    <td>{{ $att['display_name'] ?? $att['name'] ?? '-' }}</td>
                    <td class="att-date">
                        @if (!empty($att['created_at']))
                            {{ \Carbon\Carbon::parse($att['created_at'])->format('d M Y H:i') }}
                            @if (!empty($att['created_by'])) by {{ $att['created_by'] }} @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="sign-table">
        <tr>
            <td>
                <div class="sign-role">Prepared By</div>
                <div class="sign-line">{{ $createdByName }}</div>
                <div class="sign-hint">Legal Admin</div>
            </td>
            <td>
                <div class="sign-role">Reviewed By</div>
                <div class="sign-line">{!! $picLeasingNames ? e($picLeasingNames) : '&nbsp;' !!}</div>
                <div class="sign-hint">PIC Leasing</div>
            </td>
            <td>
                <div class="sign-role">Acknowledged By</div>
                <div class="sign-line">{!! $agreement->pic_penyewa ? e($agreement->pic_penyewa) : '&nbsp;' !!}</div>
                <div class="sign-hint">Tenant Representative</div>
            </td>
        </tr>
    </table>

    <div class="doc-footer">
        Generated electronically on {{ now()->format('d M Y H:i') }} &bull; {{ $company->cpny_name ?? $agreement->cpny_id }} Legal Agreement System
    </div>
</body>
</html>
