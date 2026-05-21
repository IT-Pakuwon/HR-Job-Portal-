<style>
    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        color: #000;
    }

    h2 {
        margin: 0;
        font-size: 16px;
        text-align: center;
        font-weight: bold;
    }

    .subtitle {
        text-align: center;
        font-size: 13px;
        margin-bottom: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 11px;
    }

    th {
        text-align: center;
        background: #f7f7f7;
        font-weight: bold;
    }

    .meta-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .meta-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 12px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
    }

    .meta-label {
        width: 140px;
        font-weight: bold;
        background: #f7f7f7;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 11px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
        text-align: left;
    }

    .items-table th {
        text-align: center;
        background: #f7f7f7;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .small-muted {
        font-size: 10px;
        color: #555;
    }

    .sig-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
    }

    .sig-table th,
    .sig-table td {
        border: 1px solid #000;
        padding: 6px;
        vertical-align: top;
        font-size: 11px;
    }

    .sig-table th {
        background: #f7f7f7;
        text-align: left;
        font-weight: bold;
    }

    .sig-name {
        font-weight: bold;
    }

    .sig-status {
        margin: 2px 0;
        font-size: 11px;
        font-weight: bold;
    }

    .sig-num {
        font-weight: bold;
        margin-right: 4px;
    }

    .status {
        font-weight: bold;
    }

    .blue {
        color: blue;
    }

    .red {
        color: red;
    }

    .orange {
        color: orange;
    }

    .black {
        color: black;
    }
</style>

<h2>{{ $title }}</h2>
<p class="subtitle">{{ $cpnyname }}</p>

<table class="meta-table">
    <tbody>
        <tr>
            <td class="meta-label">{{ $doc_type }} No</td>
            <td>{{ $docid }}</td>

            <td class="meta-label">Name</td>
            <td>{{ $created_by_name ?? $created_by_username }}</td>
        </tr>

        <tr>
            <td class="meta-label">{{ $doc_type }} Date</td>
            <td>{{ $spptdate }}</td>

            <td class="meta-label">Department</td>
            <td>{{ $department_id }}</td>
        </tr>

        <tr>
            <td class="meta-label">Request Type</td>
            <td>{{ $requesttype_name }}</td>

            <td class="meta-label">BQ No</td>
            <td>{{ $bqid }}</td>
        </tr>

        <tr>
            <td class="meta-label">Nama Tenant</td>
            <td>{{ $nama_tenant }}</td>

            <td class="meta-label">No Unit Tenant</td>
            <td>{{ $no_unit_tenant }}</td>
        </tr>

        <tr>
            <td class="meta-label">PIC Pengawas</td>
            <td>{{ $pic_pengawas }}</td>

            <td class="meta-label">Condition Unit</td>
            <td>{{ $condition_unit }}</td>
        </tr>

        <tr>
            <td class="meta-label">Beban</td>
            <td colspan="3">{{ $beban }}</td>
        </tr>

        <tr>
            <td class="meta-label">Keperluan</td>
            <td colspan="3">{{ $keperluan }}</td>
        </tr>
    </tbody>
</table>

<table class="items-table">
    <thead>
        <tr>
            <th style="width:25px;">No</th>
            <th>Description / Note</th>
            <th style="width:70px;">Qty / UoM</th>
            <th style="width:140px;">Location</th>
            <th style="width:140px;">Budget Department</th>
        </tr>
    </thead>

    <tbody>
        @forelse($detail as $i => $dt)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>

                <td>
                    {{ $dt->inventory_descr ?? $dt->description ?? $dt->work_description ?? '-' }}

                    @if(!empty($dt->inventoryid))
                        ({{ $dt->inventoryid }})
                    @endif

                    @if(!empty($dt->note))
                        <br>
                        <span class="small-muted">
                            Note: {{ $dt->note }}
                        </span>
                    @endif
                </td>

                <td class="text-right">
                    {{ number_format((float) ($dt->qty ?? 0), 2) }}

                    @if(!empty($dt->uom))
                        <br>
                        <span class="small-muted">
                            {{ $dt->uom }}
                        </span>
                    @endif
                </td>

                <td>
                    {{ optional($dt->location)->location_name }}

                    @if(optional($dt->subLocation)->sub_location_name)
                        - {{ optional($dt->subLocation)->sub_location_name }}
                    @endif
                </td>

                <td>
                    {{ $dt->budget_account_id ?? '-' }}

                    @if(!empty($dt->budget_activity_descr))
                        -
                        {{ $dt->budget_activity_descr }}
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No items.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@php
    $stColor = match (true) {
        in_array($status_doc, ['Approved', 'Completed']) => 'blue',
        in_array($status_doc, ['Rejected', 'Cancel']) => 'red',
        $status_doc === 'Hold' => 'orange',
        default => 'black',
    };

    $colsPerRow = $approve_count > 5 ? 4 : 3;
    $chunks = $approval->values()->chunk($colsPerRow);
    $idx = 1;
    $totalCols = 1 + $colsPerRow;
@endphp

<table class="sig-table">
    <thead>
        <tr>
            <th colspan="{{ $totalCols }}" style="text-align:left;">
                Status:
                <span class="status {{ $stColor }}">
                    {{ $status_doc }}
                </span>
            </th>
        </tr>
    </thead>

    <tbody>
        @forelse($chunks as $rowIndex => $chunk)
            <tr>
                @if($rowIndex === 0)
                    <td rowspan="{{ $chunks->count() }}" style="width:160px;">
                        <div class="sig-name">
                            {{ $created_by_name ?? $created_by_username }}
                        </div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt }}</div>
                    </td>
                @endif

                @foreach($chunk as $dt2)
                    @php
                        $label = match ($dt2->status) {
                            'A' => 'Approved',
                            'R' => 'Rejected',
                            'P' => 'Waiting',
                            default => 'Revised',
                        };

                        $color = match ($dt2->status) {
                            'A' => 'blue',
                            'R' => 'red',
                            'P' => 'orange',
                            default => 'red',
                        };

                        $dateStr = $dt2->aprv_dateafter
                            ? \Carbon\Carbon::parse($dt2->aprv_dateafter)->format('d M Y H:i')
                            : '';
                    @endphp

                    <td>
                        <div>
                            <span class="sig-num">{{ $idx++ }}.</span>
                            <span class="sig-name">{{ $dt2->aprv_name }}</span>
                        </div>
                        <div class="sig-status {{ $color }}">
                            {{ $label }}
                        </div>
                        <div>{{ $dateStr }}</div>
                    </td>
                @endforeach

                @for($i = $chunk->count(); $i < $colsPerRow; $i++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td>
                    <div class="sig-name">
                        {{ $created_by_name ?? $created_by_username }}
                    </div>
                    <div class="sig-status blue">Created</div>
                    <div>{{ $req_date_fmt }}</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>