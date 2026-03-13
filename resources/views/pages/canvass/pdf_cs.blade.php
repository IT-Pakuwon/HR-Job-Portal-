@php
    function nf($n)
    {
        return number_format((float) $n, 2);
    }
    $maxVendors = 6;
@endphp

<style>
    /* PAGE */
    @page {
        size: A3 landscape;
        margin: 6mm;
    }

    body {
        font-family: "DejaVu Sans", Arial, sans-serif;
        font-size: 10px;
        color: #000;
    }

    h2 {
        margin: 0;
        font-size: 16px;
        text-align: center;
        font-weight: bold;
    }

    .sub {
        text-align: center;
        font-size: 10px;
        margin-bottom: 10px;
    }

    /* TABLE BASE */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        table-layout: fixed;
        /* VERY IMPORTANT */
    }

    th {
        border: 0.5px solid #444;
        background: #f2f2f2;
        font-weight: 700;
        padding: 6px;
        text-align: center;
        vertical-align: middle;

        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    td {
        border: 0.5px solid #444;
        padding: 5px 6px;
        vertical-align: top;
        word-wrap: break-word;
        line-height: 1.35;

        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
    }

    th.no-col,
    td.no-col {
        width: 10px;
    }

    th.qty-col,
    td.qty-col {
        width: 20px;
    }

    th.uom-col,
    td.uom-col {
        width: 20px;
    }


    /* META INFO */
    .meta td {
        font-size: 10px;
    }

    .meta .label {
        width: 120px;
        font-weight: 700;
        background: #efefef;
        text-align: center;
    }

    .meta .value {
        text-align: left;
    }

    /* UTILITIES */
    .td-center {
        text-align: center;
    }

    .td-right {
        text-align: right;
    }

    .td-bold {
        font-weight: 700;
    }

    .muted {
        color: #666;
        font-size: 9px;
    }

    /* STATUS */
    .status {
        font-weight: 700;
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

    /* DESCRIPTION */
    .description {
        font-size: 10.5px;
    }

    /* VENDOR */
    .vendor-header {
        text-align: center;
        font-weight: 700;
    }

    .vendor-col {
        font-size: 9.5px;
        min-width: 110px;
    }

    .vendor-name {
        font-weight: 700;
        font-size: 10px;
        margin-bottom: 2px;
    }

    .vendor-info {
        font-size: 9px;
        color: #333;
        line-height: 1.3;
    }

    .tick {
        font-size: 14px;
        font-weight: 700;
        margin-top: 2px;
    }

    /* SUMMARY */
    .summary-row td {
        background: #f4f4f4;
        font-weight: 700;
    }

    .summary-row:last-child td {
        background: #e6e6e6;
        font-size: 11px;
    }

    /* APPROVAL */
    .approval-header {
        background: #f0f0f0;
        font-weight: 700;
    }
</style>

<h2>{{ $title }}</h2>
<div class="sub">{{ $cpnyname }}</div>

{{-- META --}}
<table class="meta">
    <tr>
        <td class="label">{{ $doc_type }} No</td>
        <td class="value">{{ $docid }}</td>

        <td class="label">{{ $doc_type }} Date</td>
        <td class="value">{{ $csdate }}</td>

        <td class="label">Department</td>
        <td class="value">{{ $department_id }}</td>

        <td class="label">Requester</td>
        <td class="value">{{ $user_peminta }}</td>

        <td class="label">Purchaser</td>
        <td class="value">{{ $created_by_name }}</td>
    </tr>

    @if (!empty($requesttype_name))
        <tr>
            <td class="label">Request Type</td>
            <td colspan="9" class="value">{{ $requesttype_name }}</td>
        </tr>
    @endif

    <tr>
        <td class="label">Note CS</td>
        <td colspan="9" class="value">{!! nl2br(e($keperluan)) !!}</td>
    </tr>
    {{-- <tr>
        <td class="label">Note CS</td>
        <td colspan="9" class="value">{{ $csnote }}</td>
    </tr> --}}
</table>

{{-- MAIN TABLE --}}
<table>
    <colgroup>
        <col style="width:18px"> {{-- No --}}
        <col style="width:110px"> {{-- Inventory ID --}}
        <col> {{-- Description (auto) --}}
        <col style="width:40px"> {{-- Qty --}}
        <col style="width:40px"> {{-- UoM --}}
        @for ($i = 0; $i < $maxVendors; $i++)
            <col style="width:120px"> {{-- Vendor --}}
        @endfor
        <col style="width:120px"> {{-- Budget --}}
        <col style="width:100px"> {{-- Last Price --}}
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2" class="no-col">No</th>
            <th rowspan="2" style="width:110px">Inventory ID</th>
            <th rowspan="2">Description</th>
            <th rowspan="2" class="qty-col">Qty</th>
            <th rowspan="2" class="uom-col">UoM</th>
            <th colspan="{{ $maxVendors }}">Vendor Info</th>
            <th rowspan="2" style="width:120px">Budget Account</th>
            <th rowspan="2" style="width:100px">Last Price</th>
        </tr>

        <tr>
            @for ($i = 0; $i < $maxVendors; $i++)
                <th>
                    @if (isset($vendors[$i]))
                        <div class="vendor-name">{{ $vendors[$i]['name'] }}</div>
                        @if ($vendors[$i]['top'])
                            <div class="vendor-info">Term: {{ $vendors[$i]['top'] }}</div>
                        @endif
                    @endif
                </th>
            @endfor
        </tr>
    </thead>

    <tbody>
        @foreach ($detail as $i => $dt)
            <tr>
                <td class="td-center no-col">{{ $i + 1 }}</td>
                <td>{{ $dt->inventoryid }}</td>
                <td class="description">
                    <div style="font-weight:700;">
                        {{ $dt->inventory_descr }}
                    </div>

                    @if (!empty($dt->csnote_detail))
                        <div style="margin-top:1rem;">
                            {{ $dt->csnote_detail }}
                        </div>
                    @endif
                </td>





                </td>

                <td class="td-right qty-col">{{ nf($dt->qty) }}</td>
                <td class="td-center uom-col">{{ $dt->uom }}</td>
                @for ($k = 0; $k < $maxVendors; $k++)
                    @php
                        $price = (float) ($dt->{'vendorprice' . ($k + 1)} ?? 0);
                        $total = (float) ($dt->{'vendortotalprice' . ($k + 1)} ?? 0);
                        $sel = (bool) ($dt->{'vendor' . ($k + 1) . 'selected'} ?? false);
                    @endphp
                    <td class="vendor-col td-right">
                        <div>{{ nf($price) }}</div>
                        <div class="muted">{{ nf($total) }}</div>
                        @if ($sel)
                            <div class="td-center tick">✓</div>
                        @endif
                    </td>
                @endfor

                <td class="td-center">{{ $dt->budget_account_id }}</td>
                <td class="td-center">{{ nf($dt->inventory_last_price) }}</td>
            </tr>
        @endforeach

        {{-- SUMMARY --}}
        @php
            $summaryRows = [
                'Total' => fn($k) => collect($detail)->sum(
                    fn($d) => (float) ($d->{'vendortotalprice' . ($k + 1)} ?? 0),
                ),
                'Amount Tax' => fn($k) => data_get($vendors[$k] ?? [], 'tax', 0),
                'Grand Total' => fn($k) => data_get($vendors[$k] ?? [], 'grand', 0),
                'Grand Total Selected' => fn($k) => data_get($vendors[$k] ?? [], 'grandselected', 0),
            ];
        @endphp

        @foreach ($summaryRows as $label => $calc)
            <tr class="summary-row">
                <td colspan="5" class="td-right">{{ $label }}</td>
                @for ($k = 0; $k < $maxVendors; $k++)
                    <td class="td-right vendor-col">{{ nf($calc($k)) }}</td>
                @endfor
                <td colspan="2"></td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Approvals --}}
@php
    $stColor = in_array($status_doc, ['Approved', 'Completed'])
        ? 'blue'
        : (in_array($status_doc, ['Rejected', 'Cancel'])
            ? 'red'
            : ($status_doc === 'Hold'
                ? 'orange'
                : ''));
    $colsPerRow = $approve_count > 5 ? 4 : 3;
    $chunks = $approval->values()->chunk($colsPerRow);
    $idx = 1;
@endphp

<table style="width:100%; table-layout:fixed; border-collapse:collapse;">
    <thead>
        <tr>
            <th colspan="{{ 1 + $colsPerRow }}" style="text-align:left; background:#f8f8f8;">
                Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
            </th>
        </tr>
    </thead>
    <tbody>
        @forelse($chunks as $rowIndex => $chunk)
            <tr>
                @if ($rowIndex === 0)
                    <td rowspan="{{ $chunks->count() }}" style="width:180px">
                        <div><strong>{{ $created_by_name ?? $created_by_username }}</strong></div>
                        <div class="status blue">Created</div>
                        <div class="muted">{{ $req_date_fmt }}</div>
                    </td>
                @endif

                @foreach ($chunk as $ap)
                    @php
                        $label = match ($ap->status) {
                            'A' => 'Approved',
                            'R' => 'Rejected',
                            'P' => 'Waiting',
                            default => 'Revised',
                        };
                        $color = match ($ap->status) {
                            'A' => 'blue',
                            'R' => 'red',
                            'P' => 'orange',
                            default => 'red',
                        };
                        $dateStr = $ap->aprv_dateafter
                            ? \Carbon\Carbon::parse($ap->aprv_dateafter)->format('d M Y H:i')
                            : '';
                    @endphp
                    <td>
                        <div><strong>{{ $idx++ }}. {{ $ap->aprv_name }}</strong></div>
                        <div class="status {{ $color }}">{{ $label }}</div>
                        <div class="muted">{{ $dateStr }}</div>
                    </td>
                @endforeach

                @for ($x = $chunk->count(); $x < $colsPerRow; $x++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @empty
            <tr>
                <td>
                    <div><strong>{{ $created_by_name ?? $created_by_username }}</strong></div>
                    <div class="status blue">Created</div>
                    <div class="muted">{{ $req_date_fmt }}</div>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
