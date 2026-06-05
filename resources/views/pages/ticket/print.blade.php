<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ticket->ticketid }} — IT Support Ticket</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            font-size: 11.5px;
            color: #0f172a;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page {
            max-width: 800px;
            margin: 0 auto;
            padding: 48px 52px 56px;
        }

        /* ── Document header ── */
        .doc-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 22px;
            border-bottom: 2px solid #e2e8f0;
        }

        .doc-header-left .org {
            font-size: 13px;
            font-weight: 700;
            color: #1d4ed8;
            letter-spacing: 0.01em;
        }

        .doc-header-left .meta {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .doc-header-right {
            text-align: right;
        }

        .doc-header-right .ticketid {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .doc-header-right .submitted {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .status-row {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            justify-content: flex-end;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        .badge-dot { width: 5px; height: 5px; border-radius: 50%; }

        .badge-open     { background: #dbeafe; color: #1d4ed8; }
        .badge-open     .badge-dot { background: #1d4ed8; }
        .badge-done     { background: #dcfce7; color: #15803d; }
        .badge-done     .badge-dot { background: #15803d; }
        .badge-cancel   { background: #f1f5f9; color: #64748b; }
        .badge-cancel   .badge-dot { background: #94a3b8; }
        .badge-workflow { background: #f1f5f9; color: #475569; }

        /* ── Section card ── */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .card-head {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-head.blue  { background: #eff6ff; border-bottom-color: #bfdbfe; }
        .card-head.green { background: #f0fdf4; border-bottom-color: #bbf7d0; }

        .card-head-num {
            width: 20px;
            height: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10.5px;
            font-weight: 800;
            flex-shrink: 0;
        }

        .card-head.blue  .card-head-num { background: #1d4ed8; color: #fff; }
        .card-head.green .card-head-num { background: #15803d; color: #fff; }

        .card-head-label {
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .card-head.blue  .card-head-label { color: #1d4ed8; }
        .card-head.green .card-head-label { color: #15803d; }

        /* ── Info grid ── */
        .info-grid   { display: grid; gap: 0; }
        .info-grid-2 { grid-template-columns: 1fr 1fr; }
        .info-grid-3 { grid-template-columns: 1fr 1fr 1fr; }

        .info-cell {
            padding: 10px 14px;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-grid-2 .info-cell:nth-child(2n),
        .info-grid-3 .info-cell:nth-child(3n) { border-right: none; }

        .info-label {
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 11.5px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.4;
        }

        .info-value.muted {
            color: #94a3b8;
            font-weight: 400;
            font-style: italic;
        }

        /* ── Text blocks (summary / description) ── */
        .text-block {
            padding: 10px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .text-block:last-child { border-bottom: none; }
        .text-block .info-label { margin-bottom: 6px; }

        .summary-text {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.5;
        }

        .desc-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 7px;
            padding: 10px 14px;
            font-size: 11.5px;
            color: #334155;
            line-height: 1.7;
            min-height: 34px;
        }

        .desc-box.green {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        /* Strip Quill chrome */
        .ql-editor { padding: 0 !important; min-height: unset !important; overflow: visible !important; }
        .ql-container.ql-snow, .ql-toolbar { border: none !important; }

        /* ── Priority badge ── */
        .priority-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 5px;
            font-size: 10.5px;
            font-weight: 600;
        }

        .priority-tag-dot { width: 6px; height: 6px; border-radius: 50%; }

        .p-low      { background: #dcfce7; color: #15803d; }
        .p-low      .priority-tag-dot { background: #15803d; }
        .p-medium   { background: #fef9c3; color: #854d0e; }
        .p-medium   .priority-tag-dot { background: #ca8a04; }
        .p-high     { background: #fee2e2; color: #b91c1c; }
        .p-high     .priority-tag-dot { background: #ef4444; }
        .p-critical { background: #fdf4ff; color: #7e22ce; }
        .p-critical .priority-tag-dot { background: #a855f7; }

        /* ── Signature ── */
        .sig-section {
            margin-top: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .sig-box { border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }

        .sig-box-head {
            background: #f8fafc;
            padding: 6px 14px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #64748b;
        }

        .sig-box-body { padding: 38px 14px 12px; }

        .sig-line { border-top: 1.5px solid #cbd5e1; padding-top: 5px; }
        .sig-name { font-size: 11px; font-weight: 600; color: #0f172a; }
        .sig-role  { font-size: 9.5px; color: #94a3b8; margin-top: 2px; }

        /* ── Footer ── */
        .doc-footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 9.5px;
            color: #94a3b8;
        }

        .doc-footer strong { color: #64748b; }

        @page {
            margin: 0;
        }

        @media print {
            .page { max-width: 100%; padding: 22mm 24mm 26mm; }
            a { color: inherit; text-decoration: none; }
        }
    </style>
</head>
<body>

    <script>window.onload = function () { window.print(); }</script>

    <div class="page">

        {{-- ── Document header ── --}}
        <div class="doc-header">
            <div class="doc-header-left">
                <div class="org">IT Support Ticket</div>
                <div class="meta">{{ $ticket->cpny_id }} &nbsp;·&nbsp; {{ $ticket->department_id }}</div>
            </div>
            <div class="doc-header-right">
                <div class="ticketid">{{ $ticket->ticketid }}</div>
                <div class="submitted">Submitted {{ optional($ticket->ticketdate)->format('d M Y') ?? '-' }}</div>
                @php
                    $statusLabel = match($ticket->status) {
                        'P' => 'Open', 'C' => 'Completed', 'X' => 'Cancelled', default => $ticket->status,
                    };
                    $badgeClass = match($ticket->status) {
                        'C' => 'badge-done', 'X' => 'badge-cancel', default => 'badge-open',
                    };
                @endphp
                <div class="status-row">
                    <span class="badge {{ $badgeClass }}">
                        <span class="badge-dot"></span>{{ $statusLabel }}
                    </span>
                    <span class="badge badge-workflow">{{ $ticket->status_pekerjaan }}</span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             SECTION 1 — TICKET REQUEST
        ════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-head blue">
                <div class="card-head-num">1</div>
                <div class="card-head-label">Ticket Request Information</div>
            </div>

            <div class="info-grid info-grid-3">
                <div class="info-cell">
                    <div class="info-label">Requester</div>
                    <div class="info-value">{{ $ticket->user_peminta ?? $ticket->created_by ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Company</div>
                    <div class="info-value">{{ $ticket->cpny_id ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $ticket->department_id ?? '-' }}</div>
                </div>
            </div>

            <div class="info-grid info-grid-3">
                <div class="info-cell">
                    <div class="info-label">Ticket Type</div>
                    <div class="info-value">{{ optional($ticket->type)->ticket_type_name ?? $ticket->ticket_type ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Category</div>
                    <div class="info-value">{{ optional($ticket->category)->ticket_category_name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Sub Category</div>
                    <div class="info-value">{{ optional($ticket->subcategory)->ticket_subcategory_name ?? '-' }}</div>
                </div>
            </div>

            <div class="info-grid info-grid-2">
                <div class="info-cell">
                    <div class="info-label">Location</div>
                    <div class="info-value">{{ optional($ticket->location)->location_name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Sub Location</div>
                    <div class="info-value">{{ optional($ticket->subLocation)->sub_location_name ?? '-' }}</div>
                </div>
            </div>

            <div class="text-block">
                <div class="info-label">Issue Summary</div>
                <div class="summary-text">{{ $ticket->issue_summary ?? '-' }}</div>
            </div>

            <div class="text-block">
                <div class="info-label">Issue Description</div>
                <div class="desc-box">
                    @if ($ticket->issue_descr)
                        <div class="ql-editor">{!! $ticket->issue_descr !!}</div>
                    @else
                        <span style="color:#94a3b8;font-style:italic;">No description provided.</span>
                    @endif
                </div>
            </div>

            @if (!empty($attachments))
                <div class="text-block">
                    <div class="info-label">Attachments ({{ count($attachments) }})</div>
                    @php $imageExts = ['jpg','jpeg','png']; @endphp

                    {{-- Image attachments --}}
                    @php $images = array_filter($attachments, fn($a) => in_array(strtolower($a['extention'] ?? ''), $imageExts)); @endphp
                    @if (!empty($images))
                        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px; margin-bottom:8px;">
                            @foreach ($images as $img)
                                <div style="border:1px solid #e2e8f0; border-radius:6px; overflow:hidden;">
                                    <img src="{{ $img['url'] }}" alt="{{ $img['display_name'] ?? $img['filename'] }}"
                                        style="width:100%; height:120px; object-fit:cover; display:block;">
                                    <div style="padding:5px 8px; font-size:9.5px; color:#64748b; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $img['display_name'] ?? $img['filename'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Non-image attachments --}}
                    @php $files = array_filter($attachments, fn($a) => !in_array(strtolower($a['extention'] ?? ''), $imageExts)); @endphp
                    @if (!empty($files))
                        <div style="display:flex; flex-direction:column; gap:5px;">
                            @foreach ($files as $file)
                                <div style="display:flex; align-items:center; gap:8px; padding:6px 10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px;">
                                    <span style="font-size:10px; font-weight:700; color:#64748b; background:#e2e8f0; padding:2px 6px; border-radius:4px; text-transform:uppercase;">
                                        {{ $file['extention'] ?? 'FILE' }}
                                    </span>
                                    <span style="font-size:11px; color:#334155; flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        {{ $file['display_name'] ?? $file['filename'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════
             SECTION 2 — RESOLUTION & SOLUTION
        ════════════════════════════════════════ --}}
        <div class="card">
            <div class="card-head green">
                <div class="card-head-num">2</div>
                <div class="card-head-label">Resolution &amp; Solution</div>
            </div>

            <div class="info-grid info-grid-3">
                <div class="info-cell">
                    <div class="info-label">Responded By</div>
                    <div class="info-value">{{ $respondedBy ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Completed By</div>
                    <div class="info-value {{ $ticket->completed_by ? '' : 'muted' }}">
                        {{ $ticket->completed_by ?? 'Not yet completed' }}
                    </div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Completed At</div>
                    <div class="info-value {{ $ticket->completed_at ? '' : 'muted' }}">
                        {{ optional($ticket->completed_at)->format('d M Y, H:i') ?? 'Not yet completed' }}
                    </div>
                </div>
            </div>

            <div class="info-grid info-grid-2">
                <div class="info-cell">
                    <div class="info-label">Priority</div>
                    @php
                        $pName   = optional($ticket->priority)->ticket_priority_name ?? $ticket->ticket_priority ?? '';
                        $pLower  = strtolower($pName);
                        $pClass  = match(true) {
                            str_contains($pLower, 'low')      => 'p-low',
                            str_contains($pLower, 'high')     => 'p-high',
                            str_contains($pLower, 'critical') => 'p-critical',
                            $pName !== ''                     => 'p-medium',
                            default                           => '',
                        };
                    @endphp
                    <div class="info-value" style="padding-top:3px;">
                        @if ($pName)
                            <span class="priority-tag {{ $pClass }}">
                                <span class="priority-tag-dot"></span>{{ $pName }}
                            </span>
                        @else
                            <span class="muted">Not set</span>
                        @endif
                    </div>
                </div>
                <div class="info-cell">
                    <div class="info-label">SLA Due Date</div>
                    <div class="info-value {{ $ticket->ticket_duedate ? '' : 'muted' }}">
                        {{ optional($ticket->ticket_duedate)->format('d M Y, H:i') ?? 'Not assigned' }}
                    </div>
                </div>
            </div>

            <div class="text-block">
                <div class="info-label">Solution / Resolution Description</div>
                @if ($ticket->solution_descr)
                    <div class="desc-box green">
                        <div class="ql-editor">{!! $ticket->solution_descr !!}</div>
                    </div>
                @else
                    <div class="desc-box">
                        <span style="color:#94a3b8;font-style:italic;">No solution recorded yet.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Signature (completed tickets only) ── --}}
        @if ($ticket->status === 'C')
            <div class="sig-section">
                <div class="sig-box">
                    <div class="sig-box-head">Requester</div>
                    <div class="sig-box-body">
                        <div class="sig-line">
                            <div class="sig-name">{{ $ticket->user_peminta ?? $ticket->created_by ?? '-' }}</div>
                            <div class="sig-role">Ticket Requester</div>
                        </div>
                    </div>
                </div>
                <div class="sig-box">
                    <div class="sig-box-head">IT Technician</div>
                    <div class="sig-box-body">
                        <div class="sig-line">
                            <div class="sig-name">{{ $ticket->completed_by ?? $ticket->pic_ticket ?? '-' }}</div>
                            <div class="sig-role">IT Support / PIC</div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Footer ── --}}
        <div class="doc-footer">
            <span>Generated on <strong>{{ now()->format('d M Y, H:i') }}</strong></span>
            <span><strong>{{ $ticket->ticketid }}</strong> &nbsp;·&nbsp; IT Support Ticket System</span>
        </div>

    </div>
</body>
</html>
