<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <title>
        Access Request - {{ $access->docid }}
    </title>

    <style>

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            padding:0;

            font-family:
                Arial,
                sans-serif;

            font-size:11px;

            color:#222;
        }

        .page{
            padding:28px 34px;
        }

        table{
            width:100%;

            border-collapse:collapse;
        }

        .header{
            margin-bottom:18px;
        }

        .header td{
            vertical-align:top;
        }

        .title{
            font-size:20px;

            font-weight:bold;

            letter-spacing:.8px;

            color:#111;
        }

        .company{
            margin-top:4px;

            font-size:12px;

            color:#555;
        }

        .doc-number{
            text-align:right;

            font-size:14px;

            font-weight:bold;
        }

        .doc-date{
            margin-top:4px;

            text-align:right;

            font-size:11px;

            color:#666;
        }

        .divider{
            margin-top:14px;

            border-top:
                1px solid #dcdcdc;
        }

        .meta-table{
            margin-top:18px;
            margin-bottom:22px;

            table-layout:fixed;
        }

        .meta-table td{
            border:
                1px solid #d9d9d9;

            padding:8px 10px;

            vertical-align:top;

            font-size:11px;

            word-wrap:break-word;
        }

        .meta-label{
            width:140px;

            background:#f7f7f7;

            font-weight:bold;

            color:#333;
        }

        .meta-value{
            color:#222;
        }

        .section-title{
            margin-bottom:8px;

            font-size:12px;

            font-weight:bold;

            color:#222;
        }

        .detail-table{
            margin-top:8px;
            margin-bottom:24px;

            table-layout:fixed;
        }

        .detail-table th{
            border:
                1px solid #d9d9d9;

            background:#f7f7f7;

            padding:8px;

            text-align:left;

            font-size:11px;

            font-weight:bold;

            color:#333;
        }

        .detail-table td{
            border:
                1px solid #d9d9d9;

            padding:8px;

            vertical-align:top;

            word-wrap:break-word;
        }

        .table-status{
            font-size:11px;

            font-weight:bold;
        }

        .status-approved{
            color:#1565c0;
        }

        .status-waiting{
            color:#ef6c00;
        }

        .status-rejected{
            color:#c62828;
        }

        .status-revise{
            color:#7c3aed;
        }

        .status-cancel{
            color:#616161;
        }

        .approval-table{
            margin-top:8px;

            table-layout:fixed;
        }

        .approval-table th{
            border:
                1px solid #d9d9d9;

            background:#f7f7f7;

            padding:8px;

            text-align:left;

            font-size:11px;

            font-weight:bold;

            color:#333;
        }

        .approval-table td{
            border:
                1px solid #d9d9d9;

            padding:10px;

            vertical-align:top;

            height:105px;
        }

        .approval-name{
            font-size:12px;

            font-weight:bold;

            margin-bottom:18px;

            color:#111;
        }

        .approval-status{
            font-size:11px;

            font-weight:bold;

            margin-bottom:18px;
        }

        .approval-date{
            font-size:10px;

            color:#666;
        }

        .remark-box{
            margin-top:10px;

            padding-top:8px;

            border-top:
                1px dashed #d9d9d9;

            font-size:10px;

            color:#555;

            line-height:1.5;
        }

        .footer-note{
            margin-top:14px;

            font-size:10px;

            color:#777;

            font-style:italic;
        }

    </style>

</head>

<body>

    @php

        $headerStatus = match($access->status){

            'C' => 'COMPLETED',
            'D' => 'REVISE',
            'R' => 'REJECTED',
            'X' => 'CANCELLED',

            default => 'PENDING'
        };

    @endphp

    <div class="page">

        {{-- HEADER --}}
        <table class="header">

            <tr>

                <td>

                    <div class="title">
                        ACCESS REQUEST
                    </div>

                    <div class="company">
                        {{ $company->cpny_name ?? ($access->cpny_id ?? '-') }}
                    </div>

                </td>

                <td style="text-align:right;">

                    <div class="doc-number">
                        {{ $access->docid }}
                    </div>

                    <div class="doc-date">

                        {{
                            optional($access->created_at)
                            ? \Carbon\Carbon::parse($access->created_at)->format('d F Y')
                            : '-'
                        }}

                    </div>

                </td>

            </tr>

        </table>

        <div class="divider"></div>

        {{-- META --}}
        <table class="meta-table">

            <tbody>

                <tr>

                    <td class="meta-label">
                        Requester
                    </td>

                    <td class="meta-value">
                        {{ $access->user_peminta ?? '-' }}
                    </td>

                    <td class="meta-label">
                        Department
                    </td>

                    <td class="meta-value">
                        {{ $access->department_id ?? '-' }}
                    </td>

                </tr>

                <tr>

                    <td class="meta-label">
                        Company
                    </td>

                    <td class="meta-value">
                        {{ $access->cpny_id ?? '-' }}
                    </td>

                    <td class="meta-label">
                        Request Date
                    </td>

                    <td class="meta-value">

                        {{
                            optional($access->created_at)
                            ? \Carbon\Carbon::parse($access->created_at)->format('d M Y H:i')
                            : '-'
                        }}

                    </td>

                </tr>

                <tr>

                    <td class="meta-label">
                        Request Type
                    </td>

                    <td class="meta-value">
                        {{ $access->access_type ?? '-' }}
                    </td>

                    <td class="meta-label">
                        Status
                    </td>

                    <td class="meta-value">
                        {{ $headerStatus }}
                    </td>

                </tr>

                <tr>

                    <td class="meta-label">
                        Completed By
                    </td>

                    <td class="meta-value">
                        {{ $access->completed_by ?? '-' }}
                    </td>

                    <td class="meta-label">
                        Assign To
                    </td>

                    <td class="meta-value">
                        {{ $access->user_assign ?? '-' }}
                    </td>

                </tr>

                <tr>

                    <td class="meta-label">
                        Purpose
                    </td>

                    <td class="meta-value" colspan="3">

                        {{ $access->keperluan ?? '-' }}

                    </td>

                </tr>

            </tbody>

        </table>

        {{-- DETAIL --}}
        <div class="section-title">
            Access Request Detail
        </div>

        <table class="detail-table">

            <thead>

                <tr>

                    <th width="20%">
                        Access Item
                    </th>

                    <th width="12%">
                        Category
                    </th>

                    <th width="16%">
                        Username
                    </th>

                    <th width="16%">
                        Password
                    </th>

                    <th width="22%">
                        Response
                    </th>

                    <th width="14%">
                        Status
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($details as $item)

                    @php

                        $detailStatus = match($item->status){

                            'C' => 'COMPLETED',
                            'D' => 'REVISE',
                            'R' => 'REJECTED',
                            'X' => 'CANCELLED',

                            default => 'PENDING'
                        };

                        $detailClass = match($item->status){

                            'C' => 'status-approved',
                            'D' => 'status-revise',
                            'R' => 'status-rejected',
                            'X' => 'status-cancel',

                            default => 'status-waiting'
                        };

                    @endphp

                    <tr>

                        <td>
                            {{ $item->access_descr ?? '-' }}
                        </td>

                        <td>
                            {{ $item->group_category ?? '-' }}
                        </td>

                        <td>
                            {{ $item->access_username ?? '-' }}
                        </td>

                        <td>
                            {{ $item->access_password ?? '-' }}
                        </td>

                        <td>
                            {{ $item->access_response ?? '-' }}
                        </td>

                        <td>

                            <span class="table-status {{ $detailClass }}">
                                {{ $detailStatus }}
                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6"
                            style="
                                text-align:center;
                                padding:16px;
                                color:#777;
                            ">

                            No detail available

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- APPROVAL --}}
        <div class="section-title">
            Approval Information
        </div>

        @php

            $colsPerRow = $approvals->count() > 5 ? 4 : 3;

            $chunks = $approvals->values()->chunk($colsPerRow);

        @endphp

        <table class="approval-table">

            <thead>

                <tr>

                    <th style="width:180px;">
                        Request By
                    </th>

                    @for($i = 1; $i <= $colsPerRow; $i++)

                        <th>
                            Approval
                        </th>

                    @endfor

                </tr>

            </thead>

            <tbody>

                @forelse($chunks as $rowIndex => $chunk)

                    <tr>

                        @if($rowIndex === 0)

                            <td rowspan="{{ $chunks->count() }}">

                                <div class="approval-name">

                                    {{ strtoupper($access->user_peminta ?? '-') }}

                                </div>

                                <div class="approval-status status-approved">
                                    Created
                                </div>

                                <div class="approval-date">

                                    {{
                                        optional($access->created_at)
                                        ? \Carbon\Carbon::parse($access->created_at)->format('d M Y H:i')
                                        : '-'
                                    }}

                                </div>

                            </td>

                        @endif

                        @foreach($chunk as $aprv)

                            @php

                                $label = match($aprv->status){

                                    'A' => 'Approved',
                                    'R' => 'Rejected',
                                    'P' => 'Waiting',

                                    default => 'Revise'
                                };

                                $statusClass = match($aprv->status){

                                    'A' => 'status-approved',
                                    'R' => 'status-rejected',
                                    'P' => 'status-waiting',

                                    default => 'status-revise'
                                };

                            @endphp

                            <td>

                                <div class="approval-name">

                                    {{
                                        strtoupper(
                                            $aprv->aprv_name
                                            ?? $aprv->aprv_username
                                            ?? '-'
                                        )
                                    }}

                                </div>

                                <div class="approval-status {{ $statusClass }}">
                                    {{ $label }}
                                </div>

                                <div class="approval-date">

                                    @if($aprv->updated_at)

                                        {{
                                            \Carbon\Carbon::parse($aprv->updated_at)
                                            ->format('d M Y H:i')
                                        }}

                                    @else

                                        -

                                    @endif

                                </div>

                                @if($aprv->remark)

                                    <div class="remark-box">

                                        {{ $aprv->remark }}

                                    </div>

                                @endif

                            </td>

                        @endforeach

                        @for($i = $chunk->count(); $i < $colsPerRow; $i++)

                            <td>&nbsp;</td>

                        @endfor

                    </tr>

                @empty

                    <tr>

                        <td>

                            <div class="approval-name">

                                {{ strtoupper($access->user_peminta ?? '-') }}

                            </div>

                            <div class="approval-status status-approved">
                                Created
                            </div>

                            <div class="approval-date">

                                {{
                                    optional($access->created_at)
                                    ? \Carbon\Carbon::parse($access->created_at)->format('d M Y H:i')
                                    : '-'
                                }}

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        <div class="footer-note">

            * Approval generated by Pakuwon APP System

        </div>

    </div>

</body>

</html>
