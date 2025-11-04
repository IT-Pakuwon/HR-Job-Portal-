@php
    function nf($n) { 
        return number_format((float)$n, 2); 
    }
    $maxVendors = 6; 
@endphp

<style>
    @page { 
        size: A4 landscape; 
        margin: 10mm; }

    body { 
        font-family: Arial, sans-serif; 
        font-size: 11px; 
        color:#000; 
    }

    h2 { 
        margin:0; 
        font-size:16px; 
        text-align:center; 
        font-weight:bold; 
    }

    .sub { 
        text-align:center; 
        font-size:12px; 
        margin-bottom:12px; 
    }

    table { 
        width:100%; 
        border-collapse:collapse; 
        table-layout:fixed; 
        page-break-inside:avoid; 
    }

    th, td { 
        border:1px solid #000; 
        padding:6px 8px; 
        font-size:10px; 
        vertical-align:top; 
        word-wrap:break-word; 
    }

    /* Meta Info */
    .meta td { font-size:11px; padding:5px 8px; }
    .meta .label { 
        width:130px; 
        font-weight:bold; 
        background:#f8f8f8; 
        text-align:center; 
    }
    .meta .value { 
        text-align:left; 
        vertical-align:middle; 
        word-wrap:break-word; 
        white-space:normal; 
    }

    /* Utilities */
    .td-center { text-align:center; }
    .td-right { text-align:right; }
    .td-bold { font-weight:bold; }
    .muted { color:#666; font-size:9px; }
    .tick { font-weight:bold; font-size:13px; }
    .status { font-weight:700; }
    .status.blue { color:blue; } 
    .status.red { color:red; } 
    .status.orange { color:orange; }

    /* Vendor Columns */
    .vendor-header { 
        text-align:center; 
        vertical-align:middle; 
        font-weight:bold; 
        background:#f2f2f2; 
    }
    .vendor-col { min-width:110px; max-width:130px; }
    .vendor-name { 
        font-weight:bold; 
        font-size:11px; 
        margin-bottom:4px; 
    }
    .vendor-info { 
        font-size:9px; 
        line-height:1.3; 
        color:#333; 
    }
    .vendor-info .lbl { font-weight:bold; color:#000; }

    /* Summary rows */
    .summary-row { background:#fafafa; font-weight:bold; }
</style>

<h2>{{ $title }}</h2>
<div class="sub">{{ $cpnyname }}</div>

{{-- Meta Info --}}
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

    @if(!empty($requesttype_name))
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

{{-- Main Table --}}
<table>
    <thead>
        <tr>
            <th rowspan="2" style="width:28px; text-align:center; vertical-align:middle;">No</th>
            <th rowspan="2" style="width:110px; text-align:center; vertical-align:middle;">Inventory ID</th>
            <th rowspan="2" style="text-align:center; vertical-align:middle;">Description</th>
            <th rowspan="2" style="width:52px; text-align:center; vertical-align:middle;">Qty</th>
            <th rowspan="2" style="width:52px; text-align:center; vertical-align:middle;">UoM</th>

            <th colspan="{{ $maxVendors }}" style="text-align:center; vertical-align:middle;">
                Vendor Info
            </th>

            <th rowspan="2" style="width:120px; text-align:center; vertical-align:middle;">Location</th>
            <th rowspan="2" style="width:120px; text-align:center; vertical-align:middle;">Sub Location</th>
        </tr>

        <tr>
            @for($i = 0; $i < $maxVendors; $i++)
                @if(isset($vendors[$i]))
                    <th style="vertical-align:middle; text-align:left; min-width:120px;">
                        <div class="vendor-name">{{ $vendors[$i]['name'] }}</div>
                        <div class="vendor-info">
                            @if($vendors[$i]['top'])   
                                <div><span class="lbl">Term:</span> {{ $vendors[$i]['top'] }}</div>
                            @endif
                            @if($vendors[$i]['cp'])    
                                <div><span class="lbl">CP:</span> {{ $vendors[$i]['cp'] }}</div>
                            @endif
                            @if($vendors[$i]['telp'])  
                                <div><span class="lbl">Telp:</span> {{ $vendors[$i]['telp'] }}</div>
                            @endif
                            @if($vendors[$i]['addr'])  
                                <div><span class="lbl">Alamat:</span> {{ $vendors[$i]['addr'] }}</div>
                            @endif
                        </div>
                    </th>
                @else
                    <th style="text-align:center; vertical-align:middle;">&nbsp;</th>
                @endif
            @endfor
        </tr>
    </thead>

    <tbody>
        {{-- Items --}}
        @forelse($detail as $i => $dt)
            <tr>
                <td class="td-center">{{ $i+1 }}</td>
                <td>{{ $dt->inventoryid }}</td>
                <td>{{ $dt->inventory_descr }}</td>
                <td class="td-right">{{ nf($dt->qty) }}</td>
                <td class="td-center">{{ $dt->uom }}</td>

                @for($k = 0; $k < $maxVendors; $k++)
                    @if(isset($vendors[$k]))
                        @php
                            $price = (float) ($dt->{"vendorprice".($k+1)} ?? 0);
                            $total = (float) ($dt->{"vendortotalprice".($k+1)} ?? 0);
                            $sel   = (bool) ($dt->{"vendor".($k+1)."selected"} ?? false);
                        @endphp
                        <td class="td-right vendor-col">
                            {{ nf($price) }}<br>
                            <span class="muted">{{ nf($total) }}</span>
                            @if($sel)
                                <div class="td-center"><span class="tick">✔</span></div>
                            @endif
                        </td>
                    @else
                        <td class="vendor-col">&nbsp;</td>
                    @endif
                @endfor

                <td>{{ optional($dt->location)->location_name }}</td>
                <td>{{ optional($dt->subLocation)->sub_location_name }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ 5 + $maxVendors + 2 }}" class="td-center">No items.</td>
            </tr>
        @endforelse

        {{-- Summary --}}
        @php
            $summaryRows = [
                'Total' => fn($k) => collect($detail)->sum(fn($d) => (float) ($d->{"vendortotalprice".($k+1)} ?? 0)),
                'Amount Tax' => fn($k) => data_get($vendors[$k] ?? [], 'tax', 0),
                'Grand Total' => fn($k) => data_get($vendors[$k] ?? [], 'grand', 0),
                'Grand Total Selected' => fn($k) => data_get($vendors[$k] ?? [], 'grand', 0),
            ];
        @endphp

        @foreach($summaryRows as $label => $calc)
            <tr class="summary-row">
                <td colspan="5" class="td-right">{{ $label }}</td>
                @for($k = 0; $k < $maxVendors; $k++)
                    @if(isset($vendors[$k]))
                        <td class="td-right vendor-col">{{ nf($calc($k)) }}</td>
                    @else
                        <td class="vendor-col">&nbsp;</td>
                    @endif
                @endfor
                <td colspan="2"></td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Approvals --}}
@php
    $stColor = in_array($status_doc, ['Approved','Completed']) ? 'blue'
             : (in_array($status_doc, ['Rejected','Cancel']) ? 'red'
             : ($status_doc === 'Hold' ? 'orange' : ''));
    $colsPerRow = $approve_count > 5 ? 4 : 3;
    $chunks = $approval->values()->chunk($colsPerRow);
    $idx = 1;
@endphp

<table>
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
                            default => 'Revised' 
                        };
                        $color = match ($ap->status) { 
                            'A' => 'blue', 
                            'R' => 'red', 
                            'P' => 'orange', 
                            default => 'red' 
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
