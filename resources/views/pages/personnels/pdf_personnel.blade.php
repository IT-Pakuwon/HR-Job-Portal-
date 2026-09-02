<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $personnel->docid }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 14mm;
        }

        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }

        /* ============ HEADER ============ */
        .meta-row td {
            font-size: 10px;
            color: #777;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .doc-title {
            text-align: center;
            margin-top: 8px;
        }

        .doc-title h1 {
            margin: 0;
            font-size: 19px;
            font-weight: 700;
            letter-spacing: .3px;
        }

        .doc-title .sub {
            margin-top: 3px;
            font-size: 10.5px;
            color: #777;
        }

        .header-rule {
            border: none;
            border-bottom: 2px solid #3730a3;
            margin: 14px 0 20px 0;
        }

        /* ============ SECTIONS ============ */
        .section {
            margin-top: 20px;
        }

        .section-title {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #1a1a1a;
            border-left: 3px solid #3730a3;
            border-bottom: 1px solid #eee;
            padding: 1px 0 5px 8px;
            margin-bottom: 12px;
        }

        .section-title .en {
            font-weight: 400;
            font-style: italic;
            text-transform: none;
            color: #888;
            font-size: 9.5px;
        }

        /* ============ FIELD GRID ============ */
        .field-table td {
            padding: 8px 14px 12px 0;
            border-bottom: 1px solid #ececec;
            vertical-align: top;
        }

        .field-table tr:first-child td {
            padding-top: 0;
        }

        .field-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #666;
        }

        .field-label .en {
            font-weight: 400;
            font-style: italic;
            text-transform: none;
            color: #aaa;
        }

        .field-value {
            margin-top: 4px;
            font-size: 12px;
            color: #111;
        }

        /* ============ STATS ============ */
        .stat-cell {
            padding: 4px 16px 4px 0;
        }

        .stat-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .3px;
            color: #666;
        }

        .stat-label .en {
            font-weight: 400;
            font-style: italic;
            text-transform: none;
            color: #aaa;
        }

        .stat-value {
            margin-top: 3px;
            font-size: 20px;
            font-weight: 700;
            color: #3730a3;
        }

        /* ============ LISTS ============ */
        .item-list {
            margin: 0;
            padding-left: 16px;
            font-size: 11.5px;
            line-height: 1.7;
        }

        .item-list li {
            margin-bottom: 2px;
        }

        .muted {
            color: #999;
            font-style: italic;
        }

        /* ============ APPROVAL ============ */
        .approval-table {
            table-layout: fixed;
            border-collapse: collapse;
        }

        .sign-cell {
            text-align: center;
            vertical-align: top;
            padding: 8px;
            border: 1px solid #000;
        }

        .sign-name {
            font-weight: 700;
            font-size: 10.5px;
            margin-top: 2px;
        }

        .sign-level {
            font-size: 8.5px;
            color: #888;
            margin-top: 1px;
        }

        .sign-status {
            font-size: 9px;
            font-weight: 700;
            margin-top: 3px;
        }

        .sign-date {
            font-size: 8px;
            color: #999;
            margin-top: 1px;
        }

        .blue { color: #1a4d8f; }
        .red { color: #b02a2a; }
        .orange { color: #a15c00; }

        /* ============ TOP BAR / FOOTER ============ */
        .pdf-topbar {
            position: fixed;
            top: -9mm;
            left: 0;
            right: 0;
            height: 4px;
            background: #3730a3;
        }

        .pdf-footer {
            position: fixed;
            bottom: -11mm;
            left: 0;
            right: 0;
            border-top: 1px solid #e5e5e5;
            padding-top: 6px;
            font-size: 8.5px;
            color: #999;
        }

        .pdf-footer td:last-child {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="pdf-topbar"></div>

    @php
        $fmtDate = function ($date, $format = 'd M Y') {
            if (empty($date)) return '-';
            try {
                return \Carbon\Carbon::parse($date)->format($format);
            } catch (\Throwable $e) {
                return $date;
            }
        };

        $isReplacement = strtolower((string) $personnel->job_type) === 'replacement';

        $jobresList = collect($jobres ?? []);
        $jobquaList = collect($jobqua ?? []);

        $createdSlot = ['type' => 'created', 'name' => $createdByName, 'date' => $reqDateFmt];
        $allSigners = collect([$createdSlot])->concat(collect($approval ?? [])->values());

        $renderSign = function ($slot) {
            if (($slot['type'] ?? '') === 'created') {
                echo '<div class="sign-name">'.e($slot['name']).'</div>';
                echo '<div class="sign-level">Requested By</div>';
                echo '<div class="sign-date">'.e($slot['date']).'</div>';
                return;
            }

            $ap = $slot;
            $aprvStatus = strtoupper(trim((string) $ap->status));
            $label = match ($aprvStatus) {
                'A', 'C' => 'Approved',
                'R' => 'Rejected',
                'D' => 'Revised',
                default => 'Waiting',
            };
            $color = match ($aprvStatus) {
                'A', 'C' => 'blue',
                'R', 'D' => 'red',
                default => 'orange',
            };
            $dateStr = $ap->aprv_dateafter ? \Carbon\Carbon::parse($ap->aprv_dateafter)->format('d M Y H:i') : '-';

            echo '<div class="sign-name">'.e($ap->aprv_name).'</div>';
            echo '<div class="sign-level">Level '.e($ap->aprv_leveling).'</div>';
            echo '<div class="sign-status '.$color.'">'.$label.'</div>';
            echo '<div class="sign-date">'.e($dateStr).'</div>';
        };
    @endphp

    {{-- HEADER --}}
    <table class="meta-row">
        <tr>
            <td style="text-align:left;">{{ $companyName ?: $personnel->cpnyid }}</td>
            <td style="text-align:right;">Doc No. {{ $personnel->docid }} &nbsp;&middot;&nbsp; {{ $statusDoc }}</td>
        </tr>
    </table>

    <div class="doc-title">
        <h1>Personnel Requisition Form</h1>
        <div class="sub">Daftar Permintaan Personalia &nbsp;&middot;&nbsp; PRF</div>
    </div>

    <hr class="header-rule">

    {{-- JOB INFO --}}
    <table class="field-table">
        <tr>
            <td style="width:50%;">
                <div class="field-label">Job Title <span class="en">( Nama Pekerjaan )</span></div>
                <div class="field-value">{{ $personnel->job_title ?: '-' }}</div>
            </td>
            <td style="width:50%;">
                <div class="field-label">Reason for Vacancy <span class="en">( Alasan Permintaan )</span></div>
                <div class="field-value">{{ $personnel->reason_vacancy ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Level</div>
                <div class="field-value">{{ $personnel->job_level ?: '-' }}</div>
            </td>
            <td>
                <div class="field-label">Replacement <span class="en">( Mengganti )</span></div>
                <div class="field-value">{{ $isReplacement ? ($personnel->immediate_replacement ?: '-') : '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Department <span class="en">( Departemen )</span></div>
                <div class="field-value">{{ $departmentName ?: $personnel->departementid ?: '-' }}</div>
            </td>
            <td>
                <div class="field-label">Division <span class="en">( Divisi )</span></div>
                <div class="field-value">{{ $divisionName ?: $personnel->division_id ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="field-label">Immediate Superior <span class="en">( Nama Pengawas )</span></div>
                <div class="field-value">{{ $personnel->immediate_superior ?: '-' }}</div>
            </td>
            <td>
                <div class="field-label">Superior Position <span class="en">( Jabatan Pengawas )</span></div>
                <div class="field-value">{{ $personnel->state_position ?: '-' }}</div>
            </td>
        </tr>
        @if ($personnel->other_reason)
        <tr>
            <td colspan="2">
                <div class="field-label">Other Notes <span class="en">( Alasan Lain )</span></div>
                <div class="field-value">{{ $personnel->other_reason }}</div>
            </td>
        </tr>
        @endif
    </table>

    {{-- JOB NUMBERS --}}
    <div class="section">
        <div class="section-title">Total Employees Required <span class="en">( Jumlah Personel Dibutuhkan )</span></div>
        <table>
            <tr>
                <td class="stat-cell" style="width:33%;">
                    <div class="stat-label">Total Required <span class="en">( Diinginkan )</span></div>
                    <div class="stat-value">{{ $personnel->required ?? 0 }}</div>
                </td>
                <td class="stat-cell" style="width:33%;">
                    <div class="stat-label">Actual <span class="en">( Yang Ada )</span></div>
                    <div class="stat-value">{{ $personnel->actual ?? 0 }}</div>
                </td>
                <td class="stat-cell" style="width:34%;">
                    <div class="stat-label">Actual Number <span class="en">( Seharusnya Ada )</span></div>
                    <div class="stat-value">{{ $personnel->total_actual ?? 0 }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- JOB RESPONSIBILITIES --}}
    <div class="section">
        <div class="section-title">Job Responsibilities <span class="en">( Tanggung Jawab Pekerjaan )</span></div>
        @if ($jobresList->isNotEmpty())
            <ul class="item-list">
                @foreach ($jobresList as $jr)
                    <li>{{ $jr->job_responsibilities_descr }}</li>
                @endforeach
            </ul>
        @else
            <div class="muted">No responsibilities listed.</div>
        @endif
    </div>

    {{-- JOB SPECIFICATION --}}
    <div class="section">
        <div class="section-title">Job Specification <span class="en">( Spesifikasi Pekerjaan )</span></div>
        <table class="field-table">
            <tr>
                <td style="width:50%;">
                    <div class="field-label">Education <span class="en">( Pendidikan )</span></div>
                    <div class="field-value">
                        {{ $personnel->education ?: '-' }}{{ $personnel->education_jurusan ? ' - '.$personnel->education_jurusan : '' }}
                    </div>
                </td>
                <td style="width:50%;">
                    <div class="field-label">Experience <span class="en">( Pengalaman Kerja )</span></div>
                    <div class="field-value">
                        @if ($personnel->experience_start || $personnel->experience_end)
                            {{ $personnel->experience_start }} - {{ $personnel->experience_end }} years as {{ $personnel->experience_position ?: $personnel->job_title }}
                        @else
                            -
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        @if ($jobquaList->isNotEmpty())
            <ul class="item-list" style="margin-top:10px;">
                @foreach ($jobquaList as $jq)
                    <li>{{ $jq->job_qualification_descr }}</li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- APPROVAL --}}
    <div class="section">
        <div class="section-title">Approval</div>
        <table class="approval-table">
            <tr>
                @foreach ($allSigners as $slot)
                    <td class="sign-cell">@php $renderSign($slot); @endphp</td>
                @endforeach
            </tr>
        </table>
    </div>

    <table class="pdf-footer">
        <tr>
            <td>Personnel Requisition Form &middot; {{ $personnel->docid }}</td>
            <td>Generated {{ \Carbon\Carbon::now()->format('d M Y H:i') }}</td>
        </tr>
    </table>
</body>
</html>
