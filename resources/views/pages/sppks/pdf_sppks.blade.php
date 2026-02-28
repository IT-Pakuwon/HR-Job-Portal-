<style>
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

    /* General table */
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
        /* old browser */
        white-space: normal;
        /* modern browser */
    }

    .meta-label {
        width: 140px;
        font-weight: bold;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        /* biar wrap jalan */
    }

    .items-table th,
    .items-table td {
        border: 1px solid #000;
        padding: 6px;
        font-size: 12px;
        vertical-align: top;
        word-wrap: break-word;
        white-space: normal;
        text-align: left;
        /* semua rata kiri */
    }

    .items-table th:nth-child(1),
    .items-table td:nth-child(1) {
        text-align: center;
    }

    .items-table th:nth-child(2),
    .items-table td:nth-child(2) {
        width: 100px;
    }

    .items-table th:nth-child(4),
    .items-table td:nth-child(4) {
        width: 60px;
    }

    .items-table th:nth-child(5),
    .items-table td:nth-child(5) {
        width: 60px;
    }

    .items-table th:nth-child(6),
    .items-table td:nth-child(6) {
        width: 120px;
    }

    .items-table th:nth-child(7),
    .items-table td:nth-child(7) {
        width: 140px;
    }

    /* Signature / approval table */
    /* === Signature / approval table (with border) === */
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
    }

    .sig-num {
        font-weight: bold;
        margin-right: 4px;
    }

    .status {
        font-weight: bold;
    }

    .status.blue {
        color: blue;
    }

    .status.red {
        color: red;
    }

    .status.orange {
        color: orange;
    }
</style>

<h2 style="text-align:center"><span style="font-size:16px"><strong>{{ $title }}</strong></span></h2>
<p style="text-align:center">{{ $cpnyname }}</p>


<table class="meta-table">
    <tbody>
        <tr>
            <td class="meta-label">{{ $doc_type }} No </td>
            <td>{{ $docid }}</td>
            <td class="meta-label">Name</td>
            <td>{{ $created_by_name ?? $created_by_username }}</td>
        </tr>
        <tr>
            <td class="meta-label">{{ $doc_type }} Date</td>
            <td>{{ $sppkdate }}</td>
            <td class="meta-label">Department</td>
            <td>{{ $department_id }}</td>
        </tr>
        <tr>
            <td class="meta-label">Request Type</td>
            <td>{{ $requesttype_name }}</td>
            <td class="meta-label">No Polisi</td>
            <td>{{ $no_polisi }}</td>
        </tr>
        @if (!empty($namakendaraan))
            <tr>
                <td class="meta-label">Nama Kendaraan</td>
                <td colspan="3">{{ $namakendaraan }}</td>
            </tr>
        @endif
        <tr>
            <td class="meta-label">Pemilik</td>
            <td>{{ $pemilikkendaraan }}</td>
            <td class="meta-label">KM</td>
            <td>{{ $km_kendaraan }}</td>
        </tr>
        <tr>
            <td class="meta-label">Keperluan</td>
            <td colspan="3" class="whitespace-pre-line break-words">{{ $keperluan }}</td>
        </tr>
    </tbody>
</table>


<table style="width:100%; border-collapse:collapse; border:1px solid #000;">
    <thead>
        <tr>
            <th style="text-align:center; width:15px;">No</th>
            <th style="text-align:center;">Description / Note</th>
            <th style="text-align:center; width:30px;">Qty / UoM</th>
            <th style="text-align:center; width:100px;">Location</th>
            <th style="text-align:center; width:100px;">Budget Department</th>
        </tr>
    </thead>
    <tbody>
        @forelse($detail as $i => $dt)
            <tr>
                <td style="text-align:center;">{{ $i + 1 }}</td>
                <td>{{ $dt->inventory_descr }} ( {{ $dt->inventoryid }} ) <br>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Note: {{ $dt->note }}
                    </span>
                </td>
                <td style="text-align:right;">{{ number_format((float) $dt->qty, 2) }} <br>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $dt->uom }}
                    </span>
                </td>
                <td>{{ optional($dt->location)->location_name }} - {{ optional($dt->subLocation)->sub_location_name }}
                </td>
                <td>{{ $dt->budget_account_id }} - {{ $dt->budget_activity_descr }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center;">No items.</td>
            </tr>
        @endforelse
    </tbody>
</table>



{{-- Approvals --}}
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
            <th colspan="{{ $totalCols }}" style="text-align: left;">
                Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($chunks as $rowIndex => $chunk)
            <tr>
                @if ($rowIndex === 0)
                    <td rowspan="{{ $chunks->count() }}" style="width:160px;">
                        <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
                        <div class="sig-status blue">Created</div>
                        <div>{{ $req_date_fmt }}</div>
                    </td>
                @endif

                @foreach ($chunk as $dt2)
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
                        <div><span class="sig-num">{{ $idx++ }}.</span><span
                                class="sig-name">{{ $dt2->aprv_name }}</span></div>
                        <div class="sig-status {{ $color }}">{{ $label }}</div>
                        <div>{{ $dateStr }}</div>
                    </td>
                @endforeach

                @for ($i = $chunk->count(); $i < $colsPerRow; $i++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td>
                    <div class="sig-name">{{ $created_by_name ?? $created_by_username }}</div>
                    <div class="sig-status blue">Created</div>
                    <div>{{ $req_date_fmt }}</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
