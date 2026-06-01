<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Recommendation - {{ $header->docid }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a1a; }
        .page { padding: 30px 36px; }

        .header-block { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e3a5f; padding-bottom: 12px; margin-bottom: 20px; }
        .header-block .title h1 { font-size: 16px; font-weight: 700; color: #1e3a5f; }
        .header-block .title p { font-size: 11px; color: #555; margin-top: 2px; }
        .header-block .docid { text-align: right; }
        .header-block .docid .doc-number { font-size: 14px; font-weight: 700; color: #1e3a5f; }
        .header-block .docid .doc-date { font-size: 10px; color: #777; margin-top: 2px; }

        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .status-W { background: #fef3c7; color: #92400e; }
        .status-I { background: #ffedd5; color: #9a3412; }
        .status-P { background: #dbeafe; color: #1e40af; }
        .status-C { background: #d1fae5; color: #065f46; }
        .status-R { background: #fee2e2; color: #991b1b; }
        .status-D { background: #f1f5f9; color: #475569; }
        .status-X { background: #f1f5f9; color: #64748b; }

        .section { margin-bottom: 18px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 10px; }

        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 16px; }
        .info-item .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 2px; }
        .info-item .value { font-size: 11px; color: #1e293b; word-break: break-word; }

        .recommendation-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px 14px; }
        .recommendation-box .rec-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-bottom: 4px; }
        .recommendation-box .rec-value { font-size: 11px; color: #1e293b; line-height: 1.5; white-space: pre-wrap; }

        table { width: 100%; border-collapse: collapse; }
        table thead th { background: #1e3a5f; color: #fff; font-size: 10px; font-weight: 600; text-align: left; padding: 7px 10px; }
        table tbody td { border-bottom: 1px solid #e2e8f0; padding: 7px 10px; font-size: 10px; color: #334155; vertical-align: top; }
        table tbody tr:nth-child(even) td { background: #f8fafc; }

        .approval-row td { font-size: 10px; }
        .aprv-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .aprv-P { background: #dbeafe; color: #1e40af; }
        .aprv-A { background: #d1fae5; color: #065f46; }
        .aprv-R { background: #fee2e2; color: #991b1b; }
        .aprv-D { background: #fef3c7; color: #92400e; }
        .aprv-X { background: #f1f5f9; color: #64748b; }

        .footer { border-top: 1px solid #e2e8f0; padding-top: 10px; margin-top: 24px; display: flex; justify-content: space-between; }
        .footer .note { font-size: 9px; color: #94a3b8; }
    </style>
</head>
<body>
<div class="page">

    {{-- HEADER --}}
    <div class="header-block">
        <div class="title">
            <h1>IT Recommendation Request</h1>
            <p>Pakuwon Group — IT Hardware Division</p>
        </div>
        <div class="docid">
            <div class="doc-number">{{ $header->docid }}</div>
            <div class="doc-date">
                {{ $header->itrecommend_date ? \Carbon\Carbon::parse($header->itrecommend_date)->format('d M Y') : '-' }}
            </div>
            <div style="margin-top: 4px;">
                @php
                    $statusLabels = ['W'=>'Waiting IT','I'=>'Waiting IT Revision','P'=>'Waiting Approval','C'=>'Completed','R'=>'Rejected','D'=>'Revise','X'=>'Cancelled'];
                @endphp
                <span class="status-badge status-{{ $header->status }}">
                    {{ $statusLabels[$header->status] ?? $header->status }}
                </span>
            </div>
        </div>
    </div>

    {{-- REQUEST INFORMATION --}}
    <div class="section">
        <div class="section-title">Request Information</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="label">Company</div>
                <div class="value">{{ $header->cpny_id ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Department</div>
                <div class="value">{{ $header->department_id ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Requester</div>
                <div class="value">{{ $header->user_peminta ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Ticket Number</div>
                <div class="value">{{ $header->ticketnbr ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Asset Number</div>
                <div class="value">{{ $header->assetnbr ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">IT PIC</div>
                <div class="value">{{ $header->recommend_pic ?? '-' }}</div>
            </div>
        </div>
        <div style="margin-top: 10px;">
            <div class="info-item">
                <div class="label">Purpose / Requirement</div>
                <div class="value" style="white-space: pre-wrap;">{{ $header->keperluan ?? '-' }}</div>
            </div>
        </div>
    </div>

    {{-- RECOMMENDATION --}}
    <div class="section">
        <div class="section-title">IT Recommendation</div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">
            <div class="info-item">
                <div class="label">Recommendation Type</div>
                <div class="value">{{ $header->recommend_type ?? '-' }}</div>
            </div>
            <div class="info-item">
                <div class="label">Warranty</div>
                <div class="value">{{ $header->waranty ?? '-' }}</div>
            </div>
        </div>
        @if ($header->recommendation)
        <div class="recommendation-box">
            <div class="rec-label">Recommendation Notes</div>
            <div class="rec-value">{{ $header->recommendation }}</div>
        </div>
        @endif
    </div>

    {{-- RECOMMENDATION ITEMS --}}
    @if ($details->count())
    <div class="section">
        <div class="section-title">Recommendation Items</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th>Description</th>
                    <th style="width: 50px;">Qty</th>
                    <th style="width: 60px;">UOM</th>
                    <th style="width: 90px;">Category</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $row->recommend_descr ?? '-' }}</td>
                    <td>{{ $row->qty ?? '-' }}</td>
                    <td>{{ $row->uom ?? '-' }}</td>
                    <td>{{ $row->category ?? '-' }}</td>
                    <td>{{ $row->recommend_note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- APPROVAL --}}
    @if ($approvals->count())
    <div class="section">
        <div class="section-title">Approval Workflow</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">Level</th>
                    <th>Approver</th>
                    <th style="width: 70px;">Status</th>
                    <th style="width: 100px;">Date</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvals as $row)
                <tr class="approval-row">
                    <td>{{ $row->aprv_leveling ?? '-' }}</td>
                    <td>{{ $row->aprv_username ?? '-' }}</td>
                    <td>
                        <span class="aprv-badge aprv-{{ $row->status }}">
                            @php $al = ['P'=>'Pending','A'=>'Approved','R'=>'Rejected','D'=>'Revise','X'=>'Cancelled']; @endphp
                            {{ $al[$row->status] ?? $row->status }}
                        </span>
                    </td>
                    <td>
                        {{ $row->aprv_dateafter ? \Carbon\Carbon::parse($row->aprv_dateafter)->format('d M Y H:i') : '-' }}
                    </td>
                    <td>{{ $row->aprv_purpose ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <div class="note">Printed on {{ now()->format('d M Y H:i') }}</div>
        <div class="note">{{ $header->docid }} — Pakuwon Group IT System</div>
    </div>

</div>
</body>
</html>
