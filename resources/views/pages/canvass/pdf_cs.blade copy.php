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
        table-layout: fixed;
        margin-bottom: 12px;
    }

    th {
        border: 1px solid #000;
        background: #f2f2f2;
        font-weight: 700;
        padding: 6px;
        text-align: center;
        vertical-align: middle;
    }

    td {
        border: 0.5px solid #444;
        padding: 5px 6px;
        vertical-align: top;
        word-wrap: break-word;
        line-height: 1.35;
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
        <td class="value">{{ $created_by_name ?? $created_by_username }}</td>

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
        <td class="label">Purpose</td>
        <td colspan="9" class="value">{{ $keperluan }}</td>
    </tr>
</table>

{{-- MAIN TABLE --}}
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:20px">No</th>
            <th rowspan="2" style="width:110px">Inventory ID</th>
            <th rowspan="2">Description</th>
            <th rowspan="2" style="width:50px">Qty</th>
            <th rowspan="2" style="width:50px">UoM</th>
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
                <td class="td-center">{{ $i + 1 }}</td>
                <td>{{ $dt->inventoryid }}</td>
                <td class="description">{{ $dt->inventory_descr }}</td>
                <td class="td-right">{{ nf($dt->qty) }}</td>
                <td class="td-center">{{ $dt->uom }}</td>

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

                <td>{{ $dt->budget_account_id }}</td>
                <td class="td-right">{{ nf($dt->inventory_last_price) }}</td>
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
@php
    $stColor = in_array($status_doc, ['Approved', 'Completed'])
        ? 'blue'
        : (in_array($status_doc, ['Rejected', 'Cancel'])
            ? 'red'
            : ($status_doc === 'Hold'
                ? 'orange'
                : ''));
@endphp
{{-- APPROVAL --}}
<table>
    <thead>
        <tr>
            <th colspan="5" class="approval-header">
                Status: <span class="status {{ $stColor }}">{{ $status_doc }}</span>
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($approval as $i => $ap)
            <tr>
                <td>
                    <strong>{{ $i + 1 }}. {{ $ap->aprv_name }}</strong><br>
                    <span class="status">{{ $ap->status }}</span><br>
                    <span class="muted">
                        {{ $ap->aprv_dateafter ? \Carbon\Carbon::parse($ap->aprv_dateafter)->format('d M Y H:i') : '' }}
                    </span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
